<?php

namespace presupuestos\model;

use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

require_once __DIR__ . '/MainModel.php';

class ReportsModel extends MainModel{
	/**
	 * Campos numéricos por tabla para limpiar valores monetarios y numéricos.
	 */
	private static array $numericFields = [
		'cdp' => ['valor_inicial', 'valor_operaciones', 'valor_actual', 'comprometer_saldo'],
		'pagos' => ['valor_bruto', 'valor_deducciones', 'valor_neto', 'valor_pesos'],
		'reporte_presupuestal' => ['valor_inicial', 'valor_operaciones', 'valor_actual', 'saldo_utilizar']
	];

	/**
	 * Procesa archivos Excel (.xlsx/.xls) para la semana 1.
	 * $files = [
	 *   'cdp' => $_FILES['cdp'],
	 *   'pagos' => $_FILES['pagos'],
	 *   'rp' => $_FILES['rp']
	 * ]
	 */
	public static function processWeek1Excels(array $files): array{
		$results = [];
		$mapping = [
			'cdp'   => 'cdp',
			'pagos' => 'pagos',
			'rp'    => 'reporte_presupuestal',
		];

		$hasAny = false;
		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$hasAny = true;
				break;
			}
		}
		if (!$hasAny) {
			throw new Exception('Debes seleccionar al menos un archivo Excel.');
		}

		// Validación de columnas
		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$valid = self::validateExcelColumns($files[$key]['tmp_name'], $table);
				if ($valid !== true) throw new Exception($valid);
			}
		}

		// Insertar en la BD
		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$results[] = self::importExcelToTable($files[$key]['tmp_name'], $table);
			}
		}

		return $results;
	}

	/**
	 * Obtiene columnas de una tabla (excluyendo auto_increment)
	 */
	private static function getTableColumns(string $table): array{
		$stmt = self::executeQuery("SHOW COLUMNS FROM `$table`");
		$cols = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (strpos($row['Extra'] ?? '', 'auto_increment') === false) $cols[] = $row['Field'];
		}
		return $cols;
	}

	/**
	 * Valida que cada fila del Excel tenga el número de columnas correcto
	 */
	private static function validateExcelColumns(string $filePath, string $table){
		if (!is_readable($filePath)) return "No se puede leer el archivo para '$table'.";

		$columns = self::getTableColumns($table);
		$expected = count($columns);

		$spreadsheet = IOFactory::load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		$rowNumber = 1;
		foreach ($sheet->getRowIterator() as $row) {
			if ($rowNumber === 1) {
				$rowNumber++;
				continue;
			} // saltar cabecera

			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$data = [];
			foreach ($cellIterator as $cell) $data[] = $cell->getValue();

			if (count($data) !== $expected) {
				return "Error en '$table': fila $rowNumber no coincide con columnas de la tabla ($expected esperadas).";
			}
			$rowNumber++;
		}

		return true;
	}

	/**
	 * Normaliza texto: tildes, caracteres raros, ñ -> n
	 */
	private static function normalizeText(string $text): string
	{
		$text = str_replace("\xEF\xBF\xBD", '', $text);
		$text = str_replace(['Ñ', 'ñ'], 'n', $text);
		$tildes = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'];
		$sin    = ['a', 'e', 'i', 'o', 'u', 'u', 'A', 'E', 'I', 'O', 'U', 'U'];
		return str_replace($tildes, $sin, $text);
	}

	/**
	 * Convierte a entero seguro
	 */
	private static function toNumeric($value): int
	{
		if ($value === null) return 0;
		$value = str_replace(['$', '.', ','], '', (string)$value);
		$value = trim($value);
		return is_numeric($value) ? (int)$value : 0;
	}

	/**
	 * Inserta los datos del Excel a la tabla indicada
	 */
	private static function importExcelToTable(string $filePath, string $table): string
	{
		$pdo = self::getConnection();
		$columns = self::getTableColumns($table);

		$spreadsheet = IOFactory::load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		$pdo->exec("TRUNCATE TABLE `$table`");
		$placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
		$sql = "INSERT INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		$numericCols = self::$numericFields[$table] ?? [];

		$firstRow = true;
		foreach ($sheet->getRowIterator() as $row) {
			if ($firstRow) {
				$firstRow = false;
				continue;
			} // ignorar cabecera

			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$data = [];
			foreach ($cellIterator as $cell) $data[] = $cell->getValue();

			$values = [];
			foreach ($data as $i => $val) {
				$col = $columns[$i] ?? null;
				if (!$col) continue;

				if (in_array($col, $numericCols, true)) {
					$values[] = self::toNumeric($val);
				} else {
					$val = mb_convert_encoding(trim((string)$val), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
					$values[] = self::normalizeText($val);
				}
			}
			foreach ($sheet->getRowIterator() as $row) {
				if ($firstRow) {
					$firstRow = false;
					continue;
				}

				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false);
				$data = [];
				foreach ($cellIterator as $cell) $data[] = $cell->getValue();

				$values = [];
				foreach ($data as $i => $val) {
					$col = $columns[$i] ?? null;
					if (!$col) continue;

					if (in_array($col, $numericCols, true)) {
						$values[] = self::toNumeric($val);
					} else {
						$val = mb_convert_encoding(trim((string)$val), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
						$values[] = self::normalizeText($val);
					}
				}

				// <--- Aquí pegamos el log de depuración
				error_log("Tabla: $table, Valores: " . implode(" | ", $values));

				$stmt->execute($values);
			}

			$stmt->execute($values);
		}

		return "Datos de '$table' insertados correctamente.";
	}

	/**
	 * Lista dependencias (codigo, nombre)
	 */
	public static function getDependencias(): array
	{
		$stmt = self::executeQuery("SELECT codigo, nombre FROM dependencias ORDER BY nombre ASC");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Consulta CDP por dependencia o código_cdp
	 */
	public static function consultarCDP(array $filters): array
	{
		$sql = "SELECT 
                    c.codigo_cdp                      AS numero_cdp,
                    c.fecha_registro                  AS fecha_registro,
                    d.codigo                          AS dependencia,
                    d.nombre                          AS dependencia_descripcion,
                    SUBSTRING_INDEX(c.objeto, ':', 1) AS concepto_interno,
                    c.rubro                           AS rubro,
                    c.descripcion                     AS descripcion,
                    c.fuente                          AS fuente,
                    c.valor_inicial                   AS valor_inicial,
                    c.valor_operaciones               AS valor_operaciones,
                    c.valor_actual                    AS valor_actual,
                    c.comprometer_saldo               AS saldo_por_comprometer,
                    c.objeto                          AS objeto
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

	/**
	 * Limpia datos cargados (TRUNCATE) para la semana indicada
	 */
	public static function clearWeekData(string $week): void
	{
		$tables = ['cdp', 'pagos', 'reporte_presupuestal'];
		$pdo = self::getConnection();
		try {
			$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
			foreach ($tables as $t) {
				$pdo->exec("TRUNCATE TABLE `{$t}`");
			}
			$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
		} catch (\Throwable $e) {
			throw new Exception('No se pudieron eliminar los datos: ' . $e->getMessage());
		}
	}
}
