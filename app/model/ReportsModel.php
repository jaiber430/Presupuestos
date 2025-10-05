<?php

namespace presupuestos\model;

use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

require_once __DIR__ . '/MainModel.php';


class ChunkReadFilter implements IReadFilter{
	private int $startRow = 0;
	private int $chunkSize = 0;

	public function setRows(int $startRow, int $chunkSize): void{
		$this->startRow = $startRow;
		$this->chunkSize = $chunkSize;
	}

	public function readCell($column, $row, $worksheetName = ''): bool
	{
		return $row >= $this->startRow && $row < $this->startRow + $this->chunkSize;
	}
}

class ReportsModel extends MainModel{

	private static array $numericFields = [
		'cdp' => ['valorInicial', 'valorOperaciones', 'valorActual', 'comprometerSaldo'],
		'pagos' => ['valorBruto', 'valorDeduccions', 'valorNeto', 'valorPesos', 'valorMoneda', 'valorReintegradoPesos', 'valorReintegradoMoneda'],
		'reporte_presupuestal' => ['valorInicial', 'valorOperaciones', 'valorActual', 'saldoUtilizar']
	];

	public static function processWeek1Excels(array $files, int $semanaId): array{
		$results = [];

		//Generar tabla aleatoria
		//$numeroAleatorio= rand(1, 10000); // Entre 1 y 100
		//$cdpTemporal= "cdp". $numeroAleatorio;

		//Un mapa de los archivos a procesar
		$mapping = [
			'cdp'   => "cdp",
			// 'pagos' => 'pagos',
			// 'rp'    => 'reportepresupuestal',
		];

		//self::crearTable($cdpTemporal);

		//Verifico que todos los archivos lleguén. 
		foreach ($mapping as $key => $table) {
			if (empty($files[$key]['tmp_name'])) {
				throw new Exception("No se encontro '{$table}'.xlxs.");
			}
		}


		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$valid = self::validateExcelColumns($files[$key]['tmp_name'], $table);
				if ($valid !== true) throw new Exception($valid);
			}
		}

		foreach ($mapping as $key => $table) {
			$results[] = self::importExcelToTable($files[$key]['tmp_name'], $table, $semanaId);
		}

		return $results;
	}

	private static function validateExcelColumns(string $filePath, string $table){
		if (!is_readable($filePath)) return "No se puede leer el archivo para '$table'.";

		// Columnas mínimas según tipo de Excel
		$requiredColumnsMap = [
			'cdp' => ['Numero Documento', 'Fecha de Registro', 'Fecha de Creacion', 'Obligaciones', 'Ordenes de Pagos', 'Reintegros'],

			'pagos' => ['Numero Documento', 'Fecha de Registro', 'Fecha de Creacion', 'Tipo Documento Soporte', 'Numero Documento Soporte', 'Observaciones'],

			'reportepresupuestal' => ['Numero Documento', 'Fecha de Registro', 'Fecha de Creacion', 'Tipo Doc Soporte Compromiso', 'Num Doc Soporte Compromiso', 'Objeto del Compromiso']
		];

		$requiredColumns = $requiredColumnsMap[$table];

		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		// Leer solo la primera fila (encabezados)
		$header = [];
		$firstRow = $sheet->getRowIterator(1)->current();
		$cellIterator = $firstRow->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false);
		foreach ($cellIterator as $cell) {
			$header[] = trim((string)$cell->getValue());
		}

		// Validar columnas mínimas del Excel actual
		foreach ($requiredColumns as $col) {
			if (!in_array($col, $header)) {
				return "El Excel de '$table' no parece ser correcto";
			}
		}

		return true;
	}

	private static function importExcelToTable(string $filePath, string $table, int $semanaId): string{
		$pdo = self::getConnection();
		$columns = self::getTableColumns($table);

		// Columnas que se insertan en la tabla principal. Se quitas la foraneas
		$insertColumns = array_filter($columns, fn($col) => !in_array($col, ['idCdp', 'idSemanaFk']));
		$numericCols = self::$numericFields[$table] ?? [];

		// Definir mapeo Excel -> tabla
		$mapping = [
			'main' => [
				'columns' => $insertColumns, // columnas principales
			],
			'relations' => [
				'cdpdependencia' => [
					'idCdpFk' => 'idCdp', // columna de la tabla principal
					'dependencia' => 'Dependencia',
					'descripcion' => 'Dependencia Descripcion'
				],
			]
		];

		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		// Preparar insert en tabla principal
		$finalColumns = array_merge($insertColumns, ['idSemanaFk']);
		$columnList   = "`" . implode("`,`", $finalColumns) . "`";
		$placeholders = "(" . rtrim(str_repeat("?,", count($finalColumns)), ",") . ")";
		$sql = "INSERT INTO `$table` ($columnList) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		$pdo->beginTransaction();
		$firstRow = true;
		$rowCount = 0;

		foreach ($sheet->getRowIterator() as $row) {
			if ($firstRow) {
				$firstRow = false;
				continue;
			}

			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$data = [];
			foreach ($cellIterator as $cell) $data[] = $cell->getValue();

			// Ajustar cantidad de valores
			if (count($data) < count($insertColumns)) $data = array_pad($data, count($insertColumns), null);
			elseif (count($data) > count($insertColumns)) $data = array_slice($data, 0, count($insertColumns));

			$values = [];
			$excelRow = []; // asociativo para relaciones
			foreach ($insertColumns as $i => $col) {
				$val = $data[$i] ?? null;

				// Limpiar -0
				if (trim((string)$val) === '-0') $val = null;

				if (in_array($col, $numericCols, true)) $values[] = self::toNumeric($val);
				else $values[] = self::normalizeText(mb_convert_encoding(trim((string)$val), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'));

				$excelRow[$col] = $val; // almacenar para relaciones
			}

			$values[] = $semanaId;

			// Insertar en tabla principal
			$stmt->execute($values);
			$idCdp = $pdo->lastInsertId();

			// Insertar relaciones
			foreach ($mapping['relations'] as $relTable => $relMap) {
				$relValues = [];
				$placeholders = [];
				foreach ($relMap as $colTable => $colExcel) {
					if ($colExcel === 'idCdp') $relValues[] = $idCdp; 
					else $relValues[] = $excelRow[$colExcel] ?? null;
					$placeholders[] = "?";
				}
				$sqlRel = "INSERT INTO `$relTable` (" . implode(',', array_keys($relMap)) . ") VALUES (" . implode(',', $placeholders) . ")";
				$pdo->prepare($sqlRel)->execute($relValues);
			}

			$rowCount++;
		}

		$pdo->commit();
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);

		return "Datos de '$table' insertados correctamente ($rowCount registros).";
	}


	private static function getTableColumns(string $table): array{
		$stmt = self::executeQuery("SHOW COLUMNS FROM `$table`");
		$cols = [];

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (strpos($row['Extra'] ?? '', 'auto_increment') === false) {
				$cols[] = $row['Field'];
			}
		}

		return $cols;
	}

	private static function normalizeText(string $text): string{
		$text = str_replace("\xEF\xBF\xBD", '', $text);
		$text = str_replace(['Ñ', 'ñ'], 'n', $text);
		$tildes = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'];
		$sin    = ['a', 'e', 'i', 'o', 'u', 'u', 'A', 'E', 'I', 'O', 'U', 'U'];
		return str_replace($tildes, $sin, $text);
	}

	private static function toNumeric($value): int{
		if ($value === null) return 0;
		$value = str_replace(['$', '.', ','], '', (string)$value);
		$value = trim($value);
		return is_numeric($value) ? (int)$value : 0;
	}	

	public static function getDependencias(): array{
		$stmt = self::executeQuery("SELECT codigo, nombre FROM dependencias ORDER BY nombre ASC");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function consultarCDP(array $filters): array{
		$sql = "SELECT 
                    c.codigo_cdp AS numero_cdp,
                    c.fecha_registro AS fecha_registro,
                    d.codigo AS dependencia,
                    d.nombre AS dependencia_descripcion,
                    SUBSTRING_INDEX(c.objeto, ':', 1) AS concepto_interno,
                    c.rubro, c.descripcion, c.fuente,
                    c.valor_inicial, c.valor_operaciones, c.valor_actual,
                    c.comprometer_saldo AS saldo_por_comprometer,
                    c.objeto
                FROM cdp c
                INNER JOIN dependencias d ON c.dependencia = d.codigo
                WHERE 1=1";

		$params = [];
		if (!empty($filters['codigo_cdp'])) {
			$sql .= " AND c.codigo_cdp = :codigo";
			$params[':codigo'] = $filters['codigo_cdp'];
		} elseif (!empty($filters['dependencia'])) {
			$sql .= " AND c.dependencia = :dep";
			$params[':dep'] = $filters['dependencia'];
		}

		if (empty($filters['codigo_cdp']) && empty($filters['dependencia'])) {
			$sql .= " ORDER BY c.id DESC LIMIT 200";
		}

		$stmt = self::executeQuery($sql, $params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function clearWeekData(string $week): void{
		$tables = ['cdp', 'pagos', 'reporte_presupuestal'];
		$pdo = self::getConnection();
		try {
			$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
			foreach ($tables as $t) $pdo->exec("TRUNCATE TABLE `{$t}`");
			$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
		} catch (\Throwable $e) {
			throw new Exception('No se pudieron eliminar los datos: ' . $e->getMessage());
		}
	}

	public static function crearTable(string $nombreTabla){
		$pdo = self::getConnection();
		$sql = "
        	CREATE TABLE `$nombreTabla` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `numero_documento` INT(11) NULL DEFAULT NULL,
            `fecha_registro` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `fecha_creacion` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `tipo_cdp` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `estado` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `dependencia` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `dependencia_descripcion` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `rubro` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `descripcion` TEXT NULL COLLATE 'utf8mb4_general_ci',
            `fuente` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `recurso` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `sit` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `valor_inicial` DECIMAL(18,2) NULL DEFAULT NULL,
            `valor_operaciones` DECIMAL(18,2) NULL DEFAULT NULL,
            `valor_actual` DECIMAL(18,2) NULL DEFAULT NULL,
            `saldo_por_comprometer` DECIMAL(18,2) NULL DEFAULT NULL,
            `objeto` TEXT NULL COLLATE 'utf8mb4_general_ci',
            `solicitud_CDP` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `compromiso` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `cuentas_por_pagar` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `obligaciones` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `ordenes_de_pago` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `reintegros` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `idSemanaFk` INT NOT NULL,
            `idCentroFk` INT NOT NULL
        )
        COLLATE='utf8mb4_general_ci'
        ENGINE=InnoDB";

		$stmt = $pdo->prepare($sql);

		return $stmt->execute();

	}
}
