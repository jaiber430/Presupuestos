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

	public function setRows(int $startRow, int $chunkSize): void
	{
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
		'cdp' => ['valor_inicial', 'valor_operaciones', 'valor_actual', 'comprometer_saldo'],
		'pagos' => ['valor_bruto', 'valor_deducciones', 'valor_neto', 'valor_pesos'],
		'reporte_presupuestal' => ['valor_inicial', 'valor_operaciones', 'valor_actual', 'saldo_utilizar']
	];

	public static function processWeek1Excels(array $files, int $semanaId): array{
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
		if (!$hasAny) throw new Exception('Debes seleccionar al menos un archivo Excel.');

		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$valid = self::validateExcelColumns($files[$key]['tmp_name'], $table);
				if ($valid !== true) throw new Exception($valid);
			}
		}

		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$results[] = self::importExcelToTable($files[$key]['tmp_name'], $table, $semanaId);
			}
		}

		return $results;
	}

	private static function getTableColumns(string $table): array{
		$stmt = self::executeQuery("SHOW COLUMNS FROM `$table`");
		$cols = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (strpos($row['Extra'] ?? '', 'auto_increment') === false) $cols[] = $row['Field'];
		}
		return $cols;
	}

	private static function validateExcelColumns(string $filePath, string $table){
		if (!is_readable($filePath)) return "No se puede leer el archivo para '$table'.";

		$columns = self::getTableColumns($table);
		$expected = count($columns);

		// RESTAR 1 PORQUE TODAS LAS TABLAS TIENEN semana_id
		$excelColumnsExpected = $expected - 1; // 23 columnas en el Excel

		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		$rowNumber = 1;
		foreach ($sheet->getRowIterator() as $row) {
			if ($rowNumber === 1) {
				$rowNumber++;
				continue;
			}
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$data = [];
			foreach ($cellIterator as $cell) $data[] = $cell->getValue();
			if (count($data) !== $excelColumnsExpected) {
				return "Error en '$table': fila $rowNumber no coincide con columnas del archivo ($excelColumnsExpected esperadas).";
			}
			$rowNumber++;
		}

		return true;
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

	/**
	 * Inserta los datos del Excel a la tabla indicada (fila por fila)
	 */
	private static function importExcelToTable(string $filePath, string $table, int $semanaId): string{

		$pdo = self::getConnection();
		$columns = self::getTableColumns($table);

		// Filtrar columnas para el INSERT (excluir semana_id si existe)
		$insertColumns = array_filter($columns, fn($col) => $col !== 'semana_id');
		$numericCols = self::$numericFields[$table] ?? [];

		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		// CORREGIDO: Placeholders correctos incluyendo semana_id
		$columnList = "`" . implode('`,`', $insertColumns) . "`, `semana_id`";
		$placeholders = "(" . rtrim(str_repeat("?,", count($insertColumns) + 1), ",") . ")";
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
			foreach ($cellIterator as $cell) {
				$data[] = $cell->getValue();
			}

			// Ajustar cantidad de valores
			if (count($data) < count($insertColumns)) {
				$data = array_pad($data, count($insertColumns), null);
			} elseif (count($data) > count($insertColumns)) {
				$data = array_slice($data, 0, count($insertColumns));
			}

			$values = [];
			foreach ($insertColumns as $i => $col) {
				$val = $data[$i] ?? null;
				if (in_array($col, $numericCols, true)) {
					$values[] = self::toNumeric($val);
				} else {
					$val = mb_convert_encoding(trim((string)$val), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
					$values[] = self::normalizeText($val);
				}
			}

			// Agregar el semana_id al final
			$values[] = $semanaId;

			$stmt->execute($values);
			$rowCount++;
		}

		$pdo->commit();
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);

		return "Datos de '$table' insertados correctamente ($rowCount registros).";
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

	public static function clearWeekData(string $week): void
	{
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
}
