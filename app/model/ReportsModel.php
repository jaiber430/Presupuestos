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
		'cdp' => ['valorInicial', 'valorOperaciones', 'valorActual', 'comprometerSaldo'],
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
				$results[] = self::importExcelToTableCdpOptimized($filePath, $table, $semanaId, $centroId);
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
			return "El Excel de '$table' no parece ser correcto";
		}

		return true;
	}

	private static function importExcelToTableCdpOptimized(string $filePath, string $table, int $semanaId, int $centroId): string
	{
		$pdo = self::getConnection();

		// Configurar timeouts más largos
		$pdo->setAttribute(PDO::ATTR_TIMEOUT, 600);

		$columns = self::getTableColumns($table);
		$insertColumns = array_filter($columns, fn($col) => !in_array($col, ['idCdp', 'idSemanaFk']));
		$numericCols = self::$numericFields[$table];

		// Mapeo del Excel hacia tus columnas del CDP
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
			'Valor Inicial '           => 'valorInicial',
			'Valor Operaciones '       => 'valorOperaciones',
			'Valor Actual '            => 'valorActual',
			'Saldo por Comprometer '   => 'saldoComprometer',
			'Objeto'                   => 'objeto',
			'Solicitud CDP'             => 'solicitudCdp',
			'Compromisos'               => 'compromisos',
			'Cuentas por Pagar'         => 'cuentasPagar',
			'Obligaciones'              => 'obligaciones',
			'Ordenes de Pago'           => 'ordenesPago',
			'Reintegros'                => 'reintegros'
		];

		// Configurar lectura por chunks
		$chunkSize = 1000;
		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);

		// Configurar filtro de lectura por chunks
		$chunkFilter = new ChunkReadFilter();
		$reader->setReadFilter($chunkFilter);

		// Preparar SQL statements fuera del loop
		$finalColumnsCdp = array_merge($insertColumns, ['idSemanaFk']);
		$columnList = "`" . implode("`,`", $finalColumnsCdp) . "`";
		$placeholders = "(" . rtrim(str_repeat("?,", count($finalColumnsCdp)), ",") . ")";
		$sql = "INSERT INTO `$table` ($columnList) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		// Preparar statements para dependencias
		$sqlDep = "SELECT idDependencia FROM dependencias WHERE codigo = ?";
		$stmtDep = $pdo->prepare($sqlDep);

		$sqlInsertDep = "INSERT INTO dependencias (codigo, nombre, idCentroFk) VALUES (?, ?, ?)";
		$stmtInsertDep = $pdo->prepare($sqlInsertDep);

		$sqlRel = "INSERT INTO cdpdependencia (idCdpFk, idDependenciaFk) VALUES (?, ?)";
		$stmtRel = $pdo->prepare($sqlRel);

		$totalRows = 0;
		$headers = null;

		// Leer por chunks
		for ($startRow = 2; $startRow <= 100000; $startRow += $chunkSize) {
			$chunkFilter->setRows($startRow, $chunkSize);
			$spreadsheet = $reader->load($filePath);
			$sheet = $spreadsheet->getActiveSheet();

			$pdo->beginTransaction();
			$chunkRowCount = 0;
			$hasData = false;

			foreach ($sheet->getRowIterator() as $row) {
				$rowIndex = $row->getRowIndex();
				if ($rowIndex < $startRow || $rowIndex >= $startRow + $chunkSize) {
					continue;
				}

				$data = [];
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false);

				foreach ($cellIterator as $cell) {
					$data[] = trim((string)$cell->getValue());
				}

				// Saltar fila vacía
				if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
					continue;
				}

				$hasData = true;

				// Si es la primera fila del primer chunk, obtener headers
				if ($startRow === 2 && $rowIndex === 2) {
					$headers = $data;
					continue;
				}

				if (!$headers) {
					continue;
				}

				$rowData = array_combine($headers, $data);
				if (!$rowData) {
					continue;
				}

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

				// Procesar dependencia
				$dependenciaCodigo = $rowData['Dependencia'] ?? null;
				$dependenciaDesc   = $rowData['Dependencia Descripcion'] ?? null;

				if ($dependenciaCodigo) {
					$stmtDep->execute([$dependenciaCodigo]);
					$idDependencia = $stmtDep->fetchColumn();

					if (!$idDependencia) {
						$stmtInsertDep->execute([$dependenciaCodigo, $dependenciaDesc, $centroId]);
						$idDependencia = $pdo->lastInsertId();
					}

					$stmtRel->execute([$idCdp, $idDependencia]);
				}

				$chunkRowCount++;
				$totalRows++;
			}

			$pdo->commit();
			$spreadsheet->disconnectWorksheets();
			unset($spreadsheet);

			// Si no hay datos en este chunk, terminar
			if (!$hasData) {
				break;
			}

			// Liberar memoria cada chunk
			gc_collect_cycles();
		}

		return "Datos insertados correctamente en '$table' ($totalRows registros).";
	}

	private static function importExcelToTableRpOptimized(string $filePath, string $table): string
	{
		$pdo = self::getConnection();
		$pdo->setAttribute(PDO::ATTR_TIMEOUT, 600);

		$columns = self::getTableColumns($table);
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

		// Configurar lectura por chunks
		$chunkSize = 1000;
		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);

		$chunkFilter = new ChunkReadFilter();
		$reader->setReadFilter($chunkFilter);

		// Preparar SQL statements
		$finalColumns = array_merge($insertColumns, ['idCdpFk']);
		$columnList = "`" . implode("`,`", $finalColumns) . "`";
		$placeholders = "(" . rtrim(str_repeat("?,", count($finalColumns)), ",") . ")";
		$sql = "INSERT INTO `$table` ($columnList) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		$sqlCdpDep = "SELECT idCdpFk FROM cdpdependencia cd
                      JOIN dependencias d ON cd.idDependenciaFk = d.idDependencia
                      JOIN cdp c ON cd.idCdpFk = c.idCdp
                      WHERE c.numeroDocumento = ? AND d.codigo = ?";
		$stmtCdpDep = $pdo->prepare($sqlCdpDep);

		$totalRows = 0;
		$headers = null;

		for ($startRow = 2; $startRow <= 100000; $startRow += $chunkSize) {
			$chunkFilter->setRows($startRow, $chunkSize);
			$spreadsheet = $reader->load($filePath);
			$sheet = $spreadsheet->getActiveSheet();

			$pdo->beginTransaction();
			$chunkRowCount = 0;
			$hasData = false;

			foreach ($sheet->getRowIterator() as $row) {
				$rowIndex = $row->getRowIndex();
				if ($rowIndex < $startRow || $rowIndex >= $startRow + $chunkSize) {
					continue;
				}

				$data = [];
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false);

				foreach ($cellIterator as $cell) {
					$data[] = trim((string)$cell->getValue());
				}

				if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
					continue;
				}

				$hasData = true;

				if ($startRow === 2 && $rowIndex === 2) {
					$headers = $data;
					continue;
				}

				if (!$headers) {
					continue;
				}

				$rowData = array_combine($headers, $data);
				if (!$rowData) {
					continue;
				}

				// Preparar valores para RP
				$values = [];
				foreach ($insertColumns as $col) {
					$excelCol = array_search($col, $excelMapping, true);
					$val = $excelCol && isset($rowData[$excelCol]) ? $rowData[$excelCol] : null;
					$values[] = is_numeric($val) ? self::toNumeric($val) : self::normalizeText(mb_convert_encoding($val ?? '', 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'));
				}

				// Obtener idCdpFk
				$cdpNumero      = $rowData['CDP'] ?? null;
				$dependenciaCod = $rowData['Dependencia'] ?? null;
				$idCdpFk        = null;

				if ($cdpNumero && $dependenciaCod) {
					$stmtCdpDep->execute([$cdpNumero, $dependenciaCod]);
					$idCdpFk = $stmtCdpDep->fetchColumn();
				}

				$values[] = $idCdpFk;
				$stmt->execute($values);
				$chunkRowCount++;
				$totalRows++;
			}

			$pdo->commit();
			$spreadsheet->disconnectWorksheets();
			unset($spreadsheet);

			if (!$hasData) {
				break;
			}

			gc_collect_cycles();
		}

		return "Filas insertadas en '$table' ($totalRows registros).";
	}

	private static function importExcelToTablePagosOptimized(string $filePath, string $table): string
	{
		$pdo = self::getConnection();
		$pdo->setAttribute(PDO::ATTR_TIMEOUT, 600);

		$columns = self::getTableColumns($table);
		$insertColumns = array_filter($columns, fn($col) => !in_array($col, ['idPresupuestal', 'idPresupuestalFk']));

		// Mapeo del Excel hacia tus columnas del RP
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

		// Configurar lectura por chunks
		$chunkSize = 1000;
		$reader = IOFactory::createReaderForFile($filePath);
		$reader->setReadDataOnly(true);

		$chunkFilter = new ChunkReadFilter();
		$reader->setReadFilter($chunkFilter);

		// Preparar SQL statements
		$finalColumns = array_merge($insertColumns, ['idPresupuestalFk']);
		$columnList = "`" . implode("`,`", $finalColumns) . "`";
		$placeholders = "(" . rtrim(str_repeat("?,", count($finalColumns)), ",") . ")";
		$sql = "INSERT INTO `$table` ($columnList) VALUES $placeholders";
		$stmt = $pdo->prepare($sql);

		$sqlRp = "SELECT idPresupuestal FROM reportepresupuestal WHERE compromisos = ?";
		$stmtRp = $pdo->prepare($sqlRp);

		$totalRows = 0;
		$headers = null;

		for ($startRow = 2; $startRow <= 100000; $startRow += $chunkSize) {
			$chunkFilter->setRows($startRow, $chunkSize);
			$spreadsheet = $reader->load($filePath);
			$sheet = $spreadsheet->getActiveSheet();

			$pdo->beginTransaction();
			$chunkRowCount = 0;
			$hasData = false;

			foreach ($sheet->getRowIterator() as $row) {
				$rowIndex = $row->getRowIndex();
				if ($rowIndex < $startRow || $rowIndex >= $startRow + $chunkSize) {
					continue;
				}

				$data = [];
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false);

				foreach ($cellIterator as $cell) {
					$data[] = trim((string)$cell->getValue());
				}

				if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
					continue;
				}

				$hasData = true;

				if ($startRow === 2 && $rowIndex === 2) {
					$headers = $data;
					continue;
				}

				if (!$headers) {
					continue;
				}

				$rowData = array_combine($headers, $data);
				if (!$rowData) {
					continue;
				}

				// Preparar valores para PAGOS
				$values = [];
				foreach ($insertColumns as $col) {
					$excelCol = array_search($col, $excelMapping, true);
					$val = $excelCol && isset($rowData[$excelCol]) ? $rowData[$excelCol] : null;
					$values[] = is_numeric($val) ? self::toNumeric($val) : self::normalizeText(mb_convert_encoding($val ?? '', 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'));
				}

				// Obtener idPresupuestalFk
				$compromiso = $rowData['Compromisos'] ?? null;
				$idPresupuestalFk = null;

				if ($compromiso) {
					$stmtRp->execute([$compromiso]);
					$idPresupuestalFk = $stmtRp->fetchColumn();
				}

				$values[] = $idPresupuestalFk;
				$stmt->execute($values);
				$chunkRowCount++;
				$totalRows++;
			}

			$pdo->commit();
			$spreadsheet->disconnectWorksheets();
			unset($spreadsheet);

			if (!$hasData) {
				break;
			}

			gc_collect_cycles();
		}

		return "Filas insertadas en '$table' ($totalRows registros).";
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
		$text = str_replace("\xEF\xBF\xBD", '', $text);
		$text = str_replace(['Ñ', 'ñ'], 'n', $text);
		$tildes = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'];
		$sin    = ['a', 'e', 'i', 'o', 'u', 'u', 'A', 'E', 'I', 'O', 'U', 'U'];
		return str_replace($tildes, $sin, $text);
	}

	private static function toNumeric($value): int
	{
		if ($value === null) return 0;
		$value = str_replace(['$', '.', ','], '', (string)$value);
		$value = trim($value);
		return is_numeric($value) ? (int)$value : 0;
	}

	/**
	 * Obtener dependencias
	 */
	public static function getDependencias(): array
	{
		$stmt = self::executeQuery("SELECT codigo, nombre FROM dependencias ORDER BY nombre ASC");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Obtener números CDP únicos desde numeroDocumento
	 */
	public static function getCDPs(): array
	{
		$stmt = self::executeQuery("SELECT DISTINCT numeroDocumento FROM cdp WHERE numeroDocumento IS NOT NULL AND numeroDocumento != '' ORDER BY numeroDocumento");
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Obtener conceptos internos únicos (texto antes de :)
	 */
	public static function getConceptos(): array
	{
		$stmt = self::executeQuery("SELECT DISTINCT 
					CASE 
						WHEN observaciones LIKE '%:%' THEN 
							TRIM(SUBSTRING_INDEX(observaciones, ':', 1))
						ELSE 
							TRIM(observaciones)
					END as concepto
				FROM reportepresupuestal 
				WHERE observaciones IS NOT NULL 
				AND observaciones != '' 
				ORDER BY concepto");
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * Consulta con filtros actualizados - JOIN CORREGIDO
	 */
	public static function consultarCDP(array $filters): array
	{
		$sql = "SELECT 
                c.numeroDocumento AS numero_cdp,
                c.fechaRegistro AS fecha_registro,
                d.codigo AS dependencia,
                d.nombre AS dependencia_descripcion,
                SUBSTRING_INDEX(c.objeto, ':', 1) AS concepto_interno,
                c.rubro, c.descripcionRubro, c.fuente,
                c.valorInicial AS valor_inicial, 
                c.valorOperaciones AS valor_operaciones, 
                c.valorActual AS valor_actual,
                c.saldoComprometer AS saldo_por_comprometer,
                c.objeto
            FROM cdp c
            INNER JOIN cdpdependencia cd ON c.idCdp = cd.idCdpFk
            INNER JOIN dependencias d ON cd.idDependenciaFk = d.idDependencia
            WHERE 1=1";

		$params = [];
		
		// Filtro por dependencia
		if (!empty($filters['dependencia'])) {
			$sql .= " AND d.codigo = :dependencia";
			$params[':dependencia'] = $filters['dependencia'];
		}
		
		// Filtro por número CDP
		if (!empty($filters['numero_cdp'])) {
			$sql .= " AND c.numeroDocumento = :numero_cdp";
			$params[':numero_cdp'] = $filters['numero_cdp'];
		}
		
		// Filtro por concepto interno
		if (!empty($filters['concepto_interno'])) {
			$sql .= " AND SUBSTRING_INDEX(c.objeto, ':', 1) = :concepto_interno";
			$params[':concepto_interno'] = $filters['concepto_interno'];
		}

		// Si no hay filtros específicos, limitar resultados
		if (empty($filters['dependencia']) && empty($filters['numero_cdp']) && empty($filters['concepto_interno'])) {
			$sql .= " ORDER BY c.idCdp DESC LIMIT 200";
		}

		$stmt = self::executeQuery($sql, $params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function clearWeekData(string $week): void
	{
		$tables = ['cdp', 'pagos', 'reportepresupuestal'];
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

	public static function fillInformePresupuestal(int $semanaId, int $centroId)
	{
		$pdo = self::getConnection();

		$sql = "
		INSERT INTO informepresupuestal (
		cdp,
		fechaRegistro,
		idDependenciaFK,
		rubro,
		descripcion,
		fuente,
		valorInicial,
		valorOperaciones,
		valorActual,
		saldoPorComprometer,
		valorComprometido,
		valorPagado,
		porcentajeCompromiso,
		objeto
		)
		SELECT
		c.idCdp AS cdp,
		c.fechaRegistro,
		dep.codigo AS idDependenciaFK,
		c.rubro,
		c.descripcionRubro AS descripcion,
		c.fuente,
		c.valorInicial,
		c.valorOperaciones,
		c.valorActual,
		c.valorActual - IFNULL(SUM(p.valorNeto), 0) AS saldoPorComprometer,
		c.compromisos AS valorComprometido,
		IFNULL(SUM(p.valorNeto), 0) AS valorPagado,
		IF(c.compromisos > 0, (IFNULL(SUM(p.valorNeto), 0) / c.compromisos) * 100, 0) AS porcentajeCompromiso,
		c.objeto
		FROM cdp c
		LEFT JOIN cdpdependencia cd ON cd.idCdpFk = c.idCdp
		LEFT JOIN dependencias dep ON dep.idDependencia = cd.idDependenciaFk
		LEFT JOIN pagos p ON p.cdp = c.idCdp
		WHERE c.idSemanaFk = :semanaId
		GROUP BY c.idCdp

		ON DUPLICATE KEY UPDATE
			fechaRegistro = VALUES(fechaRegistro),
			idDependenciaFK = VALUES(idDependenciaFK),
			rubro = VALUES(rubro),
			descripcion = VALUES(descripcion),
			fuente = VALUES(fuente),
			valorInicial = VALUES(valorInicial),
			valorOperaciones = VALUES(valorOperaciones),
			valorActual = VALUES(valorActual),
			saldoPorComprometer = VALUES(saldoPorComprometer),
			valorComprometido = VALUES(valorComprometido),
			valorPagado = VALUES(valorPagado),
			porcentajeCompromiso = VALUES(porcentajeCompromiso),
			objeto = VALUES(objeto);
			";

		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			':semanaId' => $semanaId
		]);
	}


	public static function updateInformeWithPagos()
	{
		$pdo = self::getConnection();

		$sql = "
			UPDATE informepresupuestal i
			LEFT JOIN reportepresupuestal r ON i.cdp = r.idCdpFk
			LEFT JOIN (
				SELECT
					cdp,
					SUM(COALESCE(valorNeto, 0)) AS sum_pago
				FROM pagos
				GROUP BY cdp
			) ps ON ps.cdp = i.cdp
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
