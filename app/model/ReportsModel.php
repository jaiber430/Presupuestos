<?php

namespace presupuestos\model;

use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

require_once __DIR__ . '/MainModel.php';


class ChunkReadFilter implements IReadFilter
{
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

class ReportsModel extends MainModel
{

	private static array $numericFields = [
		'cdp' => ['valorInicial', 'valorOperaciones', 'valorActual', 'saldoComprometer', 'reintegros'],
		'pagos' => ['valorBruto', 'valorDeduccions', 'valorNeto', 'valorPesos', 'valorMoneda', 'valorReintegradoPesos', 'valorReintegradoMoneda'],
		'reporte_presupuestal' => ['valorInicial', 'valorOperaciones', 'valorActual', 'saldoUtilizar']
	];

	public static function processWeek1Excels(array $files, int $semanaId, int $centroId): array
	{
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$results = [];

		$mapping = [
			'cdp'   => 'cdp',
			'rp'    => 'reportepresupuestal',
			'pagos' => 'pagos',
		];

		// Verificar archivos presentes
		foreach ($mapping as $key => $table) {
			if (empty($files[$key]['tmp_name'])) {
				throw new Exception("No se encontró '{$table}'.xlsx.");
			}
		}

		// 📂 Crear carpeta de almacenamiento
		$baseDir = __DIR__ . '/../storage/uploads/';
		$weekFolder = "semana_{$semanaId}_centro_{$centroId}";
		$targetDir = "{$baseDir}{$weekFolder}/";

		if (!is_dir($targetDir)) {
			mkdir($targetDir, 0777, true);
		}

		// 📁 Guardar copias y preparar rutas
		$filePaths = [];
		foreach ($mapping as $key => $table) {
			if (!empty($files[$key]['tmp_name'])) {
				$fileName = "{$table}_" . date('Ymd_His') . ".xlsx";
				$destPath = "{$targetDir}{$fileName}";

				if (!move_uploaded_file($files[$key]['tmp_name'], $destPath)) {
					throw new Exception("Error al guardar el archivo {$fileName}");
				}

				$filePaths[$key] = str_replace(__DIR__ . '/../', '', $destPath); // ruta relativa
				$files[$key]['tmp_name'] = $destPath;
			}
		}

		// 🧾 Guardar las rutas en la tabla semanascarga
		$pdo = self::getConnection();
		$stmt = $pdo->prepare("
			UPDATE semanascarga
			SET archivoCdp = :cdp,
				archivoRp = :rp,
				archivoPagos = :pagos
			WHERE idSemana = :id
			");

		$stmt->execute([
			':cdp'   => $filePaths['cdp'] ?? null,
			':rp'    => $filePaths['rp'] ?? null,
			':pagos' => $filePaths['pagos'] ?? null,
			':id'    => $semanaId,
		]);

		// Validar columnas
		foreach ($mapping as $key => $table) {
			$valid = self::validateExcelColumns($files[$key]['tmp_name'], $table);
			if ($valid !== true) throw new Exception($valid);
		}

		// Importar datos
		foreach ($mapping as $key => $table) {
			$filePath = $files[$key]['tmp_name'];

			if ($key === 'cdp') {
				$results[] = self::importExcelToTableCdp($filePath, $table, $semanaId, $centroId);
			} elseif ($key === 'rp') {
				$results[] = self::importExcelToTableRp($filePath, $table);
			} elseif ($key === 'pagos') {
				$results[] = self::importExcelToTablePagos($filePath, $table);
			}
		}

		return $results;
	}

	private static function validateExcelColumns(string $filePath, string $table)
	{
		if (!is_readable($filePath)) return "No se puede leer el archivo para '$table'.";

		$requiredColumnsMap = [
			'cdp' => ['Numero Documento', 'Fecha de Registro', 'Fecha de Creacion', 'Obligaciones', 'Ordenes de Pago', 'Reintegros'],
			'reportepresupuestal' => ['Numero Documento', 'Fecha de Registro', 'Fecha de Creacion', 'Tipo Documento Soporte', 'Numero Documento Soporte', 'Observaciones'],
			'pagos' => ['Numero Documento', 'Fecha de Registro', 'Fecha de pago', 'Compromisos', 'Cuentas por Pagar', 'Cuentas por Pagar'],

		];

		$requiredColumns = $requiredColumnsMap[$table];
		if (!$requiredColumns) return "No hay columnas definidas para la tabla '$table'.";

		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		// Leer la primera fila (encabezados)
		$header = [];
		foreach ($sheet->getColumnIterator() as $column) {
			$cell = $sheet->getCell($column->getColumnIndex() . '1');
			$value = trim((string)$cell->getValue());
			if ($value !== '') $header[] = $value;
		}

		// Buscar columnas faltantes
		$missing = [];
		foreach ($requiredColumns as $col) {
			if (!in_array($col, $header)) $missing[] = $col;
		}

		if (!empty($missing)) {
			return "El Excel de '$table' no parece ser correcto"; // Faltan: ". implode(', ', $missing) .
			//". Encabezados encontrados: " . implode(' | ', $header);
		}

		return true;
	}

	private static function importExcelToTableCdp(string $filePath, string $table, int $semanaId, int $centroId): string
	{
		$pdo = self::getConnection();
		$columns = self::getTableColumns($table);

		// Excluir columnas automáticas y FK
		$insertColumns = array_filter($columns, fn($col) => !in_array($col, ['idCdp', 'idSemanaFk']));
		$numericCols = self::$numericFields[$table];

		// Mapeo del Excel hacia tus columnas del CDP
		// En importExcelToTableCdp, actualiza el mapeo:
		$excelMapping = [
			'Numero Documento'         => 'numeroDocumento',
			'Fecha de Registro'        => 'fechaRegistro',
			'Fecha de Creacion'        => 'fechaCreacion',
			'Tipo de CDP'              => 'tipoCdp',
			'Estado'                   => 'estado',
			'Dependencia'              => 'dependencia',
			'Dependencia Descripcion'  => 'descripcion',
			'Rubro'                    => 'rubro',
			'Descripcion'              => 'descripcionRubro',
			'Fuente'                   => 'fuente',
			'Recurso'                  => 'recurso',
			'Sit'                      => 'sit',
			'Valor Inicial'            => 'valorInicial',
			'Valor Operaciones'        => 'valorOperaciones',
			'Valor Actual'             => 'valorActual',
			'Saldo por Comprometer'    => 'saldoComprometer',
			'Objeto'                   => 'objeto',
			'Solicitud CDP'            => 'solicitudCdp',
			'Compromisos'              => 'compromisos',
			'Cuentas por Pagar'        => 'cuentasPagar',
			'Obligaciones'             => 'obligaciones',
			'Ordenes de Pago'          => 'ordenesPago',
			'Reintegros'               => 'reintegros'
		];

		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		// Preparar SQL principal
		$finalColumnsCdp = array_merge($insertColumns, ['idSemanaFk']);
		$columnList = "`" . implode("`,`", $finalColumnsCdp) . "`";

		$placeholders = "(" . rtrim(str_repeat("?,", count($finalColumnsCdp)), ",") . ")";
		$sql = "INSERT INTO `$table` ($columnList) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		$pdo->beginTransaction();
		$firstRow = true;
		$rowCount = 0;

		foreach ($sheet->getRowIterator() as $row) {
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$data = [];

			foreach ($cellIterator as $cell) {
				$data[] = trim((string)$cell->getValue());
			}

			// Omitir filas vacías
			if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) continue;

			if ($firstRow) {
				$headers = $data;
				$firstRow = false;
				continue;
			}

			$rowData = array_combine($headers, $data);
			if (!$rowData) continue;

			// Preparar datos para CDP
			$values = [];
			foreach ($insertColumns as $col) {
				$excelCol = array_search($col, $excelMapping, true);
				$val = $excelCol && isset($rowData[$excelCol]) ? $rowData[$excelCol] : null;

				if (in_array($col, $numericCols, true)) {
					$values[] = self::toNumeric($val);
				} else {
					$values[] = self::normalizeText(mb_convert_encoding($val ?? '', 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'));
				}
			}

			$values[] = $semanaId;
			$stmt->execute($values);
			$idCdp = $pdo->lastInsertId();

			// Relacionar dependencia
			$dependenciaCodigo = $rowData['Dependencia'];
			$dependenciaDesc   = $rowData['Dependencia Descripcion'];

			if ($dependenciaCodigo) {
				$sqlDep = "SELECT idDependencia FROM dependencias WHERE codigo = ?";
				$stmtDep = $pdo->prepare($sqlDep);
				$stmtDep->execute([$dependenciaCodigo]);
				$idDependencia = $stmtDep->fetchColumn();

				if (!$idDependencia) {
					$sqlInsertDep = "INSERT INTO dependencias (codigo, nombre, idCentroFk) VALUES (?, ?, ?)";
					$pdo->prepare($sqlInsertDep)->execute([$dependenciaCodigo, $dependenciaDesc, $centroId]);
					$idDependencia = $pdo->lastInsertId();
				}

				$sqlRel = "INSERT INTO cdpdependencia (idCdpFk, idDependenciaFk) VALUES (?, ?)";
				$pdo->prepare($sqlRel)->execute([$idCdp, $idDependencia]);
			}

			$rowCount++;
		}

		$pdo->commit();
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);

		return "Datos insertados correctamente en '$table' ($rowCount registros).";
	}

	private static function importExcelToTableRp(string $filePath, string $table)
	{
		$pdo = self::getConnection();
		$columns = self::getTableColumns($table);

		// Excluir columnas automáticas y FK
		$insertColumns = array_filter($columns, fn($col) => !in_array($col, ['idPresupuestal', 'idCdpFk']));

		// Mapeo del Excel hacia tus columnas del RP
		$excelMapping = [
			'Numero Documento'         => 'numeroDocumento',
			'Fecha de Registro'        => 'fechaRegistro',
			'Fecha de Creacion'        => 'fechaCreacion',
			'Estado'                   => 'estado',
			'Dependencia'              => 'dependencia',
			'Dependencia Descripcion'  => 'descripcion',
			'Rubro'                    => 'rubro',
			'Descripcion'              => 'descripcionRubro',
			'Fuente'                   => 'fuente',
			'Recurso'                  => 'recurso',
			'Situacion'                => 'situacion',
			'Valor Inicial'            => 'valorInicial',
			'Valor Operaciones'        => 'valorOperaciones',
			'Valor Actual'             => 'valorActual',
			'Saldo por Utilizar'       => 'saldoUtilizar',
			'Tipo Identificacion'      => 'tipoIdentificacion',
			'Identificacion'           => 'identificacion',
			'Nombre Razon Social'      => 'nombreRazonSocial',
			'Medio de Pago'            => 'medioPago',
			'Tipo Cuenta'              => 'tipoCuenta',
			'Numero Cuenta'            => 'numeroCuenta',
			'Estado Cuenta'            => 'estadoCuenta',
			'Entidad Nit'              => 'entidadNit',
			'Entidad Descripcion'      => 'entidadDescripcion',
			'CDP'                      => 'numeroDocumento',
			'Compromisos'              => 'compromisos',
			'Cuentas por Pagar'        => 'cuentasPagar',
			'Obligaciones'             => 'obligaciones',
			'Ordenes de Pago'          => 'ordenesPago',
			'Reintegros'               => 'reintegros',
			'Fecha Documento Soporte'  => 'fechaSoporte',
			'Tipo Documento Soporte'   => 'tipoDocumentoSoporte',
			'Numero Documento Soporte' => 'numeroDocumentoSoporte',
			'Observaciones'            => 'observaciones'
		];

		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		// Preparar SQL principal
		$finalColumns = array_merge($insertColumns, ['idCdpFk']);
		$columnList = "`" . implode("`,`", $finalColumns) . "`";
		$placeholders = "(" . rtrim(str_repeat("?,", count($finalColumns)), ",") . ")";
		$sql = "INSERT INTO `$table` ($columnList) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		$pdo->beginTransaction();
		$firstRow = true;
		$rowCount = 0;

		foreach ($sheet->getRowIterator() as $row) {
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$data = [];

			foreach ($cellIterator as $cell) {
				$data[] = trim((string)$cell->getValue());
			}

			// Saltar fila vacía
			if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) continue;

			if ($firstRow) {
				$headers = $data;
				$firstRow = false;
				continue;
			}

			$rowData = array_combine($headers, $data);
			if (!$rowData) continue;

			// Preparar valores para RP
			$values = [];
			foreach ($insertColumns as $col) {
				$excelCol = array_search($col, $excelMapping, true);
				$val = $excelCol && isset($rowData[$excelCol]) ? $rowData[$excelCol] : null;

				$values[] = is_numeric($val) ? self::toNumeric($val) : self::normalizeText(mb_convert_encoding($val ?? '', 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'));
			}

			// Obtener idCdpFk a partir del CDP y la dependencia
			$cdpNumero      = $rowData['CDP'] ?? null;
			$dependenciaCod = $rowData['Dependencia'] ?? null;
			$idCdpFk        = null;

			if ($cdpNumero && $dependenciaCod) {
				$sqlCdpDep = "SELECT idCdpFk FROM cdpdependencia cd
                  JOIN dependencias d ON cd.idDependenciaFk = d.idDependencia
                  JOIN cdp c ON cd.idCdpFk = c.idCdp
                  WHERE c.numeroDocumento = ? AND d.codigo = ?";
				$stmtCdpDep = $pdo->prepare($sqlCdpDep);
				$stmtCdpDep->execute([$cdpNumero, $dependenciaCod]);
				$idCdpFk = $stmtCdpDep->fetchColumn();
			}


			$values[] = $idCdpFk;
			$stmt->execute($values);
			$rowCount++;
		}

		$pdo->commit();
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);

		return "Filas incertadas en '$table' ($rowCount registros).";
	}

	private static function importExcelToTablePagos(string $filePath, string $table): string
	{
		$pdo = self::getConnection();
		$columns = self::getTableColumns($table);

		// Excluir columnas automáticas y FK
		$insertColumns = array_filter($columns, fn($col) => !in_array($col, ['idPagos', 'idCdpFk']));

		$excelMapping = [
			'Numero Documento'               => 'numeroDocumento',
			'Fecha de Registro'              => 'fechaRegistro',
			'Fecha de pago'                  => 'fechaPago',
			'Estado'                         => 'estado',
			'Valor Bruto'                    => 'valorBruto',
			'Valor Deducciones'              => 'valorDeducciones',
			'Valor Neto'                     => 'valorNeto',
			'Tipo Beneficiario'              => 'tipoBeneficiario',
			'Vigencia Presupuestal'          => 'vigenciaPresupuestal',
			'Tipo Identificacion'            => 'tipoIdentificacion',
			'Identificacion'                 => 'identificacion',
			'Nombre Razon Social'            => 'nombreRazonSocial',
			'Medio de Pago'                  => 'medioPago',
			'Tipo Cuenta'                    => 'tipoCuenta',
			'Numero Cuenta'                  => 'numeroCuenta',
			'Estado Cuenta'                  => 'estadoCuenta',
			'Entidad Nit'                    => 'entidadNit',
			'Entidad Descripcion'            => 'entidadDescripcion',
			'Dependencia'                    => 'dependencia',
			'Dependencia Descripcion'        => 'dependenciaDescripcion',
			'Rubro'                          => 'rubro',
			'Descripcion'                    => 'descripcionRubro',
			'Fuente'                         => 'fuente',
			'Recurso'                        => 'recurso',
			'Sit'                            => 'situacion',
			'Valor Pesos'                    => 'valorPesos',
			'Valor Moneda'                   => 'valorMoneda',
			'Valor Reintegrado Pesos'        => 'valorReintegradoPesos',
			'Valor Reintegrado Moneda'       => 'valorReintegradoMoneda',
			'Tesoreria Pagadora'             => 'tesoreriaPagadora',
			'Identificacion Pagaduria'       => 'identificacionPagaduria',
			'Cuenta Pagaduria'               => 'cuentaPagaduria',
			'Endosada'                       => 'endosada',
			'Tipo Identificacion.1'          => 'tipoIdentificacionEndoso',
			'Identificacion.1'               => 'identificacionEndoso',
			'Razon social'                   => 'razonSocialEndoso',
			'Numero Cuenta.1'                => 'numeroCuentaEndoso',
			'Concepto Pago'                  => 'conceptoPago',
			'Solicitud CDP'                  => 'solicitudCdp',
			'CDP'                            => 'cdp',
			'Compromisos'                    => 'compromisos',
			'Cuentas por Pagar'              => 'cuentasPorPagar',
			'Fecha Cuentas por Pagar'        => 'fechaCuentasPorPagar',
			'Obligaciones'                   => 'obligaciones',
			'Ordenes de Pago'                => 'ordenesDePago',
			'Reintegros'                     => 'reintegros',
			'Fecha Doc Soporte Compromiso'   => 'fechaDocSoporteCompromiso',
			'Tipo Doc Soporte Compromiso'    => 'tipoDocSoporteCompromiso',
			'Num Doc Soporte Compromiso'     => 'numDocSoporteCompromiso',
			'Objeto del Compromiso'          => 'objetoCompromiso'
		];

		// ✅ VERIFICACIÓN: Asegurar que $excelMapping es un array
		if (!is_array($excelMapping)) {
			throw new Exception("Error: excelMapping no es un array válido");
		}

		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();

		// Preparar SQL principal
		$finalColumns = array_merge($insertColumns, ['idCdpFk']);
		$columnList = "`" . implode("`,`", $finalColumns) . "`";
		$placeholders = "(" . rtrim(str_repeat("?,", count($finalColumns)), ",") . ")";
		$sql = "INSERT INTO `$table` ($columnList) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		$pdo->beginTransaction();
		$firstRow = true;
		$rowCount = 0;

		foreach ($sheet->getRowIterator() as $row) {
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$data = [];

			foreach ($cellIterator as $cell) {
				$data[] = trim((string)$cell->getValue());
			}

			// ✅ Validación mejorada de filas vacías
			$isEmptyRow = true;
			foreach ($data as $value) {
				$cleanValue = preg_replace('/\s+/', '', (string)$value);
				if (!empty($cleanValue) && $cleanValue !== '-' && $cleanValue !== 'N/A') {
					$isEmptyRow = false;
					break;
				}
			}

			if ($isEmptyRow) continue;

			if ($firstRow) {
				$headers = $data;
				$firstRow = false;
				continue;
			}

			$rowData = array_combine($headers, $data);
			if (!$rowData) continue;

			// ✅ Validar que tenga datos críticos antes de insertar
			$cdpNumero = $rowData['CDP'] ?? null;
			$compromiso = $rowData['Compromisos'] ?? null;
			$valorNeto = $rowData['Valor Neto'] ?? null;

			// Si no tiene CDP, Compromiso NI Valor Neto, saltar la fila
			if (empty($cdpNumero) && empty($compromiso) && empty($valorNeto)) {
				continue;
			}

			// ✅ PREPARAR VALORES CON VERIFICACIÓN DE SEGURIDAD
			$values = [];
			foreach ($insertColumns as $col) {
				// Verificación de seguridad para array_search
				$excelCol = null;
				if (is_array($excelMapping)) {
					$excelCol = array_search($col, $excelMapping, true);
				}

				$val = ($excelCol !== false && isset($rowData[$excelCol])) ? $rowData[$excelCol] : null;

				$values[] = is_numeric($val) ? self::toNumeric($val) : self::normalizeText(mb_convert_encoding($val ?? '', 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'));
			}

			// Obtener idCdpFk a partir del CDP y la dependencia
			$cdpNumero = $rowData['CDP'] ?? null;
			$dependenciaCod = $rowData['Dependencia'] ?? null;
			$idCdpFk = null;

			if ($cdpNumero && $dependenciaCod) {
				$sqlCdpDep = "SELECT idCdpFk FROM cdpdependencia cd
                        JOIN dependencias d ON cd.idDependenciaFk = d.idDependencia
                        JOIN cdp c ON cd.idCdpFk = c.idCdp
                        WHERE c.numeroDocumento = ? AND d.codigo = ?";
				$stmtCdpDep = $pdo->prepare($sqlCdpDep);
				$stmtCdpDep->execute([$cdpNumero, $dependenciaCod]);
				$idCdpFk = $stmtCdpDep->fetchColumn();
			}

			$values[] = $idCdpFk;

			// ✅ Verificar que al menos algunos valores no estén vacíos
			$nonEmptyValues = array_filter($values, function ($v) {
				return !empty($v) && $v !== '' && $v !== null;
			});

			if (count($nonEmptyValues) > 1) { // Más de 1 porque idCdpFk podría ser NULL
				$stmt->execute($values);
				$rowCount++;
			}
		}

		$pdo->commit();
		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);

		return "Filas insertadas en '$table' ($rowCount registros).";
	}

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

	private static function normalizeText(string $text): string
	{
		if ($text === null || $text === '') return '';

		$text = mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
		$text = str_replace("\xEF\xBF\xBD", '', $text); // Remove replacement characters

		// Mantener ñ y Ñ
		$tildes = [
			'á' => 'a',
			'é' => 'e',
			'í' => 'i',
			'ó' => 'o',
			'ú' => 'u',
			'ü' => 'u',
			'Á' => 'A',
			'É' => 'E',
			'Í' => 'I',
			'Ó' => 'O',
			'Ú' => 'U',
			'Ü' => 'U'
		];

		return strtr($text, $tildes);
	}

	private static function toNumeric($value): float
	{
		if ($value === null || $value === '' || $value === '-') {
			return 0;
		}

		$value = trim((string)$value);
		if ($value === '-') return 0;

		// Eliminar símbolos de moneda
		$value = str_replace(['$', ' ', '€', 'USD'], '', $value);

		// Manejar diferentes formatos numéricos
		if (preg_match('/^\d{1,3}(?:\.\d{3})*,\d+$/', $value)) {
			$value = str_replace('.', '', $value);
			$value = str_replace(',', '.', $value);
		} elseif (preg_match('/^\d{1,3}(?:,\d{3})*\.\d+$/', $value)) {
			$value = str_replace(',', '', $value);
		} elseif (preg_match('/^\d+,\d+$/', $value)) {
			$value = str_replace(',', '.', $value);
		}

		return is_numeric($value) ? (float)$value : 0;
	}

	public static function getDependencias(): array
	{
		$stmt = self::executeQuery("SELECT codigo, nombre FROM dependencias ORDER BY nombre ASC");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function consultarCDP(array $filters): array
	{
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

	public static function crearTable(string $nombreTabla)
	{
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

	public static function fillInformePresupuestal()
	{
		$pdo = self::getConnection();

		try {
			// PRIMERO: Limpiar la tabla
			$pdo->exec("TRUNCATE TABLE informepresupuestal");

			// SEGUNDO: Insertar datos con consulta directa y segura
			$sql = "
        INSERT INTO informepresupuestal (
            cdp, fechaRegistro, idDependenciaFK, rubro, descripcion,
            fuente, valorInicial, valorOperaciones, valorActual,
            saldoPorComprometer, valorComprometido, valorPagado, 
            porcentajeCompromiso, objeto
        )
        SELECT
            c.numeroDocumento AS cdp,
            c.fechaRegistro,
            COALESCE(dep.codigo, 'SIN-DEP') AS idDependenciaFK,
            c.rubro,
            c.descripcionRubro AS descripcion,
            c.fuente,
            c.valorInicial,
            c.valorOperaciones,
            c.valorActual,
            GREATEST(c.valorActual - COALESCE(pt.total_pagado, 0), 0) AS saldoPorComprometer,
            c.compromisos AS valorComprometido,
            COALESCE(pt.total_pagado, 0) AS valorPagado,
            CASE 
                WHEN c.compromisos > 0 THEN (COALESCE(pt.total_pagado, 0) / c.compromisos) * 100
                ELSE 0 
            END AS porcentajeCompromiso,
            c.objeto
        FROM cdp c
        LEFT JOIN (
            SELECT idCdpFk, SUM(COALESCE(valorNeto, 0)) as total_pagado
            FROM pagos 
            GROUP BY idCdpFk
        ) pt ON pt.idCdpFk = c.idCdp
        LEFT JOIN cdpdependencia cd ON cd.idCdpFk = c.idCdp
        LEFT JOIN dependencias dep ON dep.idDependencia = cd.idDependenciaFk
        WHERE c.numeroDocumento IS NOT NULL
        AND c.numeroDocumento != ''
        ";

			$result = $pdo->exec($sql);
			return "Informe presupuestal actualizado. Filas afectadas: " . $result;
		} catch (Exception $e) {
			error_log("ERROR en fillInformePresupuestal: " . $e->getMessage());
			return "Error: " . $e->getMessage();
		}
	}

	public static function updateInformeWithPagos()
	{
		$pdo = self::getConnection();

		$sql = "
        UPDATE informepresupuestal i
        LEFT JOIN cdp c ON i.cdp = c.numeroDocumento 
        LEFT JOIN reportepresupuestal r ON c.idCdp = r.idCdpFk  -- ✅ Ahora usamos c.idCdp para unir con r.idCdpFk
        LEFT JOIN (
            SELECT
                idCdpFk,
                SUM(COALESCE(valorNeto, 0)) AS sum_pago
            FROM pagos
            GROUP BY idCdpFk
        ) ps ON ps.idCdpFk = c.idCdp  
        SET
            i.valorComprometido = COALESCE(r.compromisos, i.valorComprometido),
            i.valorPagado = COALESCE(ps.sum_pago, 0),
            i.porcentajeCompromiso = CASE
                WHEN COALESCE(r.compromisos, 0) > 0
                THEN (COALESCE(ps.sum_pago, 0) / r.compromisos) * 100
                ELSE 0
            END
        ";

		$pdo->exec($sql);
	}
}
