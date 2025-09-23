<?php
namespace presupuestos\model;

use Exception;
use PDO;

require_once __DIR__ . '/MainModel.php';

class ReportsModel extends MainModel
{
	/**
	 * Campos numéricos por tabla para limpiar valores monetarios y numéricos.
	 */
	private static array $numericFields = [
		'cdp' => ['valor_inicial', 'valor_operaciones', 'valor_actual', 'comprometer_saldo'],
		'pagos' => ['valor_bruto', 'valor_deducciones', 'valor_neto', 'valor_pesos'],
		'reporte_presupuestal' => ['valor_inicial', 'valor_operaciones', 'valor_actual', 'saldo_utilizar']
	];

	public static function processWeek1CSVs(array $files): array
	{
		$results = [];

		$mapping = [
			'cdp'   => 'cdp',
			'pagos' => 'pagos',
			'rp'    => 'reporte_presupuestal',
		];

		// Validar que al menos 1 archivo fue seleccionado
		$hasAny = false;
		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) { $hasAny = true; break; }
		}
		if (!$hasAny) {
			throw new Exception('Debes seleccionar al menos un archivo CSV.');
		}

		// Validación previa: si se cargan varios, validar todos antes de insertar
		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$valid = self::validateCSVColumns($files[$key]['tmp_name'], $table);
				if ($valid !== true) {
					throw new Exception($valid);
				}
			}
		}

		// Procesamiento
		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$msg = self::importCSVToTable($files[$key]['tmp_name'], $table);
				$results[] = $msg;
			}
		}

		return $results;
	}

	/**
	 * Obtiene columnas de una tabla (excluyendo auto_increment) en orden.
	 */
	private static function getTableColumns(string $table): array
	{
		$stmt = self::executeQuery("SHOW COLUMNS FROM `$table`");
		$cols = [];
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if (strpos($row['Extra'] ?? '', 'auto_increment') === false) {
				$cols[] = $row['Field'];
			}
		}
		return $cols;
	}

	/**
	 * Valida que cada fila del CSV tenga el número de columnas que la tabla requiere
	 * (excluyendo el id autoincrement si aplica). Retorna true o un mensaje de error.
	 */
	private static function validateCSVColumns(string $filePath, string $table)
	{
		if (!is_readable($filePath)) {
			return "No se puede leer el archivo para '$table'.";
		}

		$cols = self::getTableColumns($table);
		$expected = count($cols);

		if (($handle = fopen($filePath, 'r')) === false) {
			return "No se pudo abrir el archivo para '$table'.";
		}

		// Descartar cabecera
		fgetcsv($handle, 10000, ',');
		$line = 2;
		while (($data = fgetcsv($handle, 10000, ',')) !== false) {
			if (count($data) !== $expected) {
				fclose($handle);
				return "Error en '$table': la fila $line no coincide con las columnas de la tabla ($expected esperadas).";
			}
			$line++;
		}

		fclose($handle);
		return true;
	}

	/**
	 * Limpia y normaliza una cadena (acentos, caracteres raros, Ñ -> n).
	 */
	private static function normalizeText(string $text): string
	{
		$text = str_replace("\xEF\xBF\xBD", '', $text); // reemplazar �
		$text = str_replace(['Ñ','ñ'], 'n', $text);
		$tildes = ['á','é','í','ó','ú','ü','Á','É','Í','Ó','Ú','Ü'];
		$sin    = ['a','e','i','o','u','u','A','E','I','O','U','U'];
		$text = str_replace($tildes, $sin, $text);
		return $text;
	}

	/**
	 * Quita símbolos y convierte en entero seguro.
	 */
	private static function toNumeric($value): int
	{
		if ($value === null) return 0;
		$value = str_replace(['$', '.', ','], '', (string)$value);
		$value = trim($value);
		return is_numeric($value) ? (int)$value : 0;
	}

	/**
	 * Inserta el CSV a la tabla indicada. Trunca tabla antes (como en la prueba).
	 */
	private static function importCSVToTable(string $filePath, string $table): string
	{
		$pdo = self::getConnection();
		$cols = self::getTableColumns($table);
		if (empty($cols)) {
			throw new Exception("No se encontraron columnas para la tabla $table");
		}

		if (($handle = fopen($filePath, 'r')) === false) {
			throw new Exception("No se pudo abrir el archivo para '$table'.");
		}

		// Truncar tabla antes de insertar (comportamiento de prueba)
		$pdo->exec("TRUNCATE TABLE `$table`");

		// Descartar cabecera
		fgetcsv($handle, 10000, ',');

		$placeholders = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
		$sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		$numericForTable = self::$numericFields[$table] ?? [];

		while (($data = fgetcsv($handle, 10000, ',')) !== false) {
			$values = [];
			foreach ($data as $i => $val) {
				$col = $cols[$i];
				if (in_array($col, $numericForTable, true)) {
					$values[] = self::toNumeric($val);
				} else {
					$val = mb_convert_encoding(trim((string)$val), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
					$val = self::normalizeText($val);
					$values[] = $val;
				}
			}
			$stmt->execute($values);
		}

		fclose($handle);
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
	 * Consulta CDP por dependencia o por codigo_cdp (al menos uno de los dos).
	 * Filtros: ['dependencia' => string, 'codigo_cdp' => string]
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

		// Si no hay filtros, limitar para evitar respuestas enormes
		if (empty($filters['codigo_cdp']) && empty($filters['dependencia'])) {
			$sql .= " ORDER BY c.id DESC LIMIT 200";
		}

		$stmt = self::executeQuery($sql, $params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}

