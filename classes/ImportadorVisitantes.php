<?php
/**
 * Clase para importación masiva de visitantes desde CSV/Excel
 */
class ImportadorVisitantes {
    private $db;
    private $errores = [];
    private $procesados = 0;
    private $importados = 0;
    private $actualizados = 0;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Procesar archivo CSV
     */
    public function procesarCSV(string $archivo, array $mapeoColumnas, array $opciones = []): array {
        $this->resetearContadores();
        
        if (!file_exists($archivo)) {
            throw new Exception("Archivo no encontrado: $archivo");
        }
        
        $handle = fopen($archivo, 'r');
        if (!$handle) {
            throw new Exception("No se pudo abrir el archivo");
        }
        
        // Configuraciones por defecto
        $delimitador = $opciones['delimitador'] ?? ',';
        $encoding = $opciones['encoding'] ?? 'UTF-8';
        $saltarPrimeraFila = $opciones['saltar_primera_fila'] ?? true;
        $eventoId = $opciones['evento_id'] ?? null;
        $actualizarExistentes = $opciones['actualizar_existentes'] ?? false;
        
        $filaActual = 0;
        
        while (($fila = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
            $filaActual++;
            
            // Saltar cabecera si está configurado
            if ($saltarPrimeraFila && $filaActual === 1) {
                continue;
            }
            
            $this->procesados++;
            
            try {
                // Mapear datos según configuración
                $datosVisitante = $this->mapearDatos($fila, $mapeoColumnas);
                
                // Validar datos
                $erroresValidacion = $this->validarDatos($datosVisitante);
                if (!empty($erroresValidacion)) {
                    $this->errores[] = "Fila $filaActual: " . implode(', ', $erroresValidacion);
                    continue;
                }
                
                // Separar datos de ubicación (mesa/asiento/lugar/zona) si vienen mapeados
                $datosUbicacion = [];
                foreach (['mesa','asiento','lugar','zona'] as $k) {
                    if (isset($datosVisitante[$k])) {
                        $datosUbicacion[$k] = $datosVisitante[$k];
                        unset($datosVisitante[$k]);
                    }
                }

                // Procesar visitante
                $resultado = $this->procesarVisitante($datosVisitante, $actualizarExistentes);
                
                if ($resultado['accion'] === 'creado') {
                    $this->importados++;
                } elseif ($resultado['accion'] === 'actualizado') {
                    $this->actualizados++;
                }
                
                // Inscribir a evento si se especifica
                if ($eventoId && $resultado['visitante_id']) {
                    $this->inscribirAEvento($resultado['visitante_id'], $eventoId, $filaActual, $datosUbicacion);
                }
                
            } catch (Exception $e) {
                $this->errores[] = "Fila $filaActual: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        return $this->obtenerResultado();
    }
    
    /**
     * Mapear datos de la fila según configuración
     */
    private function mapearDatos(array $fila, array $mapeoColumnas): array {
        $datos = [];
        
        foreach ($mapeoColumnas as $campo => $indiceColumna) {
            if (is_numeric($indiceColumna) && isset($fila[$indiceColumna])) {
                $valor = trim($fila[$indiceColumna]);
                if (!empty($valor)) {
                    $datos[$campo] = $valor;
                }
            }
        }
        
        return $datos;
    }
    
    /**
     * Validar datos del visitante
     */
    private function validarDatos(array $datos): array {
        $errores = [];
        
        // Campos obligatorios
        if (empty($datos['nombre'])) {
            $errores[] = "Nombre es obligatorio";
        }
        
        if (empty($datos['apellido'])) {
            $errores[] = "Apellido es obligatorio";
        }
        
        if (empty($datos['email'])) {
            $errores[] = "Email es obligatorio";
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "Email no es válido";
        }
        
        // Validar RUT si existe
        if (!empty($datos['rut'])) {
            $datos['rut'] = $this->limpiarRut($datos['rut']);
            if (!$this->validarRut($datos['rut'])) {
                $errores[] = "RUT no es válido";
            }
        }
        
        // Validar teléfono
        if (!empty($datos['telefono'])) {
            $datos['telefono'] = preg_replace('/[^0-9+\-\s]/', '', $datos['telefono']);
        }
        
        return $errores;
    }
    
    /**
     * Procesar un visitante individual
     */
    private function procesarVisitante(array $datos, bool $actualizar = false): array {
        $visitante = new Visitante();
        
        // Verificar si existe por email
        $existente = $visitante->obtenerPorEmail($datos['email']);
        
        if ($existente) {
            if ($actualizar) {
                // Actualizar datos existentes
                $visitante->actualizar($existente['id'], $datos);
                return [
                    'accion' => 'actualizado',
                    'visitante_id' => $existente['id']
                ];
            } else {
                throw new Exception("Email ya existe: {$datos['email']}");
            }
        } else {
            // Crear nuevo visitante
            $visitanteId = $visitante->crear($datos);
            return [
                'accion' => 'creado',
                'visitante_id' => $visitanteId
            ];
        }
    }
    
    /**
     * Inscribir visitante a evento
     */
    private function inscribirAEvento(int $visitanteId, int $eventoId, int $fila, array $ubicacion = []): void {
        try {
            $inscripcion = new Inscripcion();
            $inscripcionId = $inscripcion->crear($eventoId, $visitanteId);
            
            // Confirmar automáticamente
            $inscripcion->confirmar($inscripcionId);
            // Aplicar ubicación si se proporcionó
            if (!empty($ubicacion)) {
                $set = [];
                $params = [];
                foreach (['mesa','asiento','lugar','zona'] as $f) {
                    if (isset($ubicacion[$f]) && $ubicacion[$f] !== '') {
                        $set[$f] = $ubicacion[$f];
                    }
                }
                if (!empty($set)) {
                    $this->db->update('inscripciones', $set, 'id = ?', [$inscripcionId]);
                }
            }
            
        } catch (Exception $e) {
            // Si falla la inscripción, solo agregar a errores pero no detener el proceso
            $this->errores[] = "Fila $fila: Error inscripción - " . $e->getMessage();
        }
    }
    
    /**
     * Limpiar RUT
     */
    private function limpiarRut(string $rut): string {
        $rut = strtoupper(trim($rut));
        $rut = preg_replace('/[^0-9K\-]/', '', $rut);
        
        // Agregar guión si no lo tiene
        if (strlen($rut) > 1 && strpos($rut, '-') === false) {
            $rut = substr($rut, 0, -1) . '-' . substr($rut, -1);
        }
        
        return $rut;
    }
    
    /**
     * Validar RUT chileno
     */
    private function validarRut(string $rut): bool {
        if (empty($rut)) return true; // RUT es opcional
        
        $rut = $this->limpiarRut($rut);
        
        if (!preg_match('/^(\d{1,8})-([0-9K])$/', $rut, $matches)) {
            return false;
        }
        
        $numero = $matches[1];
        $dv = $matches[2];
        
        // Calcular dígito verificador
        $suma = 0;
        $multiplicador = 2;
        
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += $numero[$i] * $multiplicador;
            $multiplicador = $multiplicador == 7 ? 2 : $multiplicador + 1;
        }
        
        $resto = $suma % 11;
        $dvCalculado = 11 - $resto;
        
        if ($dvCalculado == 11) $dvCalculado = '0';
        if ($dvCalculado == 10) $dvCalculado = 'K';
        
        return $dv == $dvCalculado;
    }
    
    /**
     * Generar template CSV
     */
    public function generarTemplateCSV(): string {
        $headers = [
            'nombre',
            'apellido', 
            'email',
            'telefono',
            'empresa',
            'cargo',
            'rut',
            'mesa',
            'asiento',
            'lugar',
            'zona'
        ];
        
        $ejemplos = [
            ['Juan', 'Pérez', 'juan.perez@empresa.com', '+56912345678', 'Empresa ABC', 'Gerente', '12345678-9', '12', 'A3', 'Salón Principal', 'VIP'],
            ['María', 'González', 'maria.gonzalez@corp.cl', '56987654321', 'Corporación XYZ', 'Directora', '98765432-1', '8', 'B1', 'Patio Central', 'General'],
            ['Carlos', 'López', 'carlos.lopez@startup.cl', '912345678', 'Startup Tech', 'Desarrollador', '11111111-1', '', '', 'Auditorio 2', 'Balcón']
        ];
        
        $csvContent = implode(',', $headers) . "\n";
        
        foreach ($ejemplos as $ejemplo) {
            $csvContent .= '"' . implode('","', $ejemplo) . '"' . "\n";
        }
        
        return $csvContent;
    }
    
    /**
     * Detectar separador CSV
     */
    public function detectarSeparador(string $archivo): string {
        $handle = fopen($archivo, 'r');
        $primeraLinea = fgets($handle);
        fclose($handle);
        
        $separadores = [',', ';', '\t', '|'];
        $conteos = [];
        
        foreach ($separadores as $sep) {
            $conteos[$sep] = substr_count($primeraLinea, $sep);
        }
        
        return array_search(max($conteos), $conteos);
    }
    
    /**
     * Obtener preview del archivo
     */
    public function obtenerPreview(string $archivo, string $delimitador = ',', int $filas = 5): array {
        $handle = fopen($archivo, 'r');
        $preview = [];
        $contador = 0;
        
        while (($fila = fgetcsv($handle, 1000, $delimitador)) !== FALSE && $contador < $filas) {
            $preview[] = $fila;
            $contador++;
        }
        
        fclose($handle);
        return $preview;
    }
    
    /**
     * Resetear contadores
     */
    private function resetearContadores(): void {
        $this->errores = [];
        $this->procesados = 0;
        $this->importados = 0;
        $this->actualizados = 0;
    }
    
    /**
     * Obtener resultado del procesamiento
     */
    public function obtenerResultado(): array {
        return [
            'procesados' => $this->procesados,
            'importados' => $this->importados,
            'actualizados' => $this->actualizados,
            'errores' => $this->errores,
            'errores_count' => count($this->errores),
            'exitosos' => $this->importados + $this->actualizados
        ];
    }
}
