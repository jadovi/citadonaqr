<?php
require_once 'config/config.php';

$error = null;
$eventoData = null;
$inscripcion = null;

// Validar acceso por hash del evento
if (!isset($_GET['access']) || empty($_GET['access'])) {
    $error = 'Enlace de acceso no válido';
} else {
    try {
        $hashAcceso = $_GET['access'];
        $evento = new Evento();
        $eventoData = $evento->obtenerPorHashAcceso($hashAcceso);
        if (!$eventoData) {
            $error = 'Evento no encontrado o no activo';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Si se envió RUT, buscar inscripción confirmada para el evento
if (!$error && ($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['rut'])) {
    $rut = trim($_POST['rut']);
    // Normalizar RUT: quitar puntos, mayúsculas, dejar guión antes del dígito verificador
    $rut = strtoupper(str_replace(['.', ' '], '', $rut));
    if (strpos($rut, '-') === false && strlen($rut) > 1) {
        $rut = substr($rut, 0, -1) . '-' . substr($rut, -1);
    }
    try {
        $db = Database::getInstance();
        $sql = "
            SELECT i.*, v.nombre, v.apellido, v.email, v.empresa, v.cargo, v.rut,
                   e.nombre AS evento_nombre, e.empresa AS evento_empresa, e.fecha_inicio, e.fecha_fin
            FROM inscripciones i
            JOIN visitantes v ON v.id = i.visitante_id
            JOIN eventos e ON e.id = i.evento_id
            WHERE e.hash_acceso = ? AND v.rut = ?
            LIMIT 1
        ";
        $inscripcion = $db->fetch($sql, [$hashAcceso, $rut]);
        if (!$inscripcion) {
            $error = 'No encontramos una inscripción para este RUT en este evento.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = $eventoData ? ($eventoData['nombre'] . ' - Mi QR') : 'Mi QR';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="qrcodejs.min.js"></script>
    <style>
        body{background:#f1f3f5}
        .card-elev{box-shadow:0 10px 30px rgba(0,0,0,.08)}
        .qr-box{background:#fff;border:3px solid #e9ecef;border-radius:16px;padding:16px}
        #qrcode{min-height:250px;display:flex;align-items:center;justify-content:center}
    </style>
    </style>
    </head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>"><i class="bi bi-qr-code"></i> Acreditación</a>
        </div>
    </nav>

    <div class="container py-4">
        <?php if ($error && !$inscripcion): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($eventoData): ?>
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card card-elev">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Evento</h5>
                        <div class="text-muted mb-2"><?= htmlspecialchars($eventoData['empresa']) ?></div>
                        <div class="fw-bold"><?= htmlspecialchars($eventoData['nombre']) ?></div>
                        <small class="text-muted"><?= date('d/m/Y', strtotime($eventoData['fecha_inicio'])) ?><?php if($eventoData['fecha_fin']!==$eventoData['fecha_inicio']):?> - <?= date('d/m/Y', strtotime($eventoData['fecha_fin'])) ?><?php endif; ?></small>
                    </div>
                </div>

                <?php if (!$inscripcion): ?>
                <div class="card card-elev mt-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-card-text"></i> Buscar mi QR por RUT</h5>
                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">RUT</label>
                                <input type="text" name="rut" class="form-control" placeholder="12.345.678-9" required autofocus>
                                <small class="text-muted">Ingrese su RUT tal como fue inscrito</small>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-7">
                <?php if ($inscripcion): ?>
                    <div class="card card-elev">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Mi Ticket</h5>
                                <?php
                                    $estado = $inscripcion['estado'];
                                    $badge = 'secondary';
                                    $txt = '';
                                    if ($estado === 'confirmado') { $badge = 'success'; $txt = 'Confirmado'; }
                                    elseif ($estado === 'pendiente') { $badge = 'warning'; $txt = 'Pendiente'; }
                                    elseif ($estado === 'cancelado') { $badge = 'danger'; $txt = 'Cancelado'; }
                                ?>
                                <span class="badge bg-<?= $badge ?>">Estado: <?= $txt ?></span>
                            </div>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="fw-bold"><?= htmlspecialchars($inscripcion['nombre'] . ' ' . $inscripcion['apellido']) ?></div>
                                    <div class="text-muted"><?= htmlspecialchars($inscripcion['email']) ?></div>
                                    <?php if (!empty($inscripcion['empresa'])): ?>
                                        <div class="text-muted"><i class="bi bi-building"></i> <?= htmlspecialchars($inscripcion['empresa']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($inscripcion['mesa'])): ?>
                                        <div class="mt-2"><span class="badge bg-primary"><i class="bi bi-grid-3x3-gap"></i> Mesa: <?= htmlspecialchars($inscripcion['mesa']) ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($inscripcion['asiento'])): ?>
                                        <div class="mt-2"><span class="badge bg-secondary"><i class="bi bi-chair"></i> Asiento: <?= htmlspecialchars($inscripcion['asiento']) ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($inscripcion['lugar'])): ?>
                                        <div class="mt-2"><span class="badge bg-info text-dark"><i class="bi bi-geo-alt"></i> Lugar: <?= htmlspecialchars($inscripcion['lugar']) ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($inscripcion['zona'])): ?>
                                        <div class="mt-2"><span class="badge bg-dark"><i class="bi bi-diagram-3"></i> Zona: <?= htmlspecialchars($inscripcion['zona']) ?></span></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="qr-box">
                                        <div id="qrcode"><div class="text-muted">Generando QR...</div></div>
                                    </div>
                                    <div class="text-center mt-2"><small class="text-muted"><i class="bi bi-shield-check"></i> QR dinámico, se renueva cada 7s</small></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function(){
                            renderQR('<?= htmlspecialchars($inscripcion['codigo_qr']) ?>','<?= htmlspecialchars($inscripcion['id']) ?>','<?= htmlspecialchars($hashAcceso) ?>');
                            startRefresh('<?= htmlspecialchars($inscripcion['codigo_qr']) ?>','<?= htmlspecialchars($inscripcion['id']) ?>','<?= htmlspecialchars($hashAcceso) ?>');
                        });
                    </script>
                <?php else: ?>
                    <div class="card card-elev">
                        <div class="card-body text-center py-5 text-muted">
                            <i class="bi bi-qr-code" style="font-size:3rem"></i>
                            <p class="mt-3 mb-0">Ingrese su RUT para ver su código QR</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</body>
<script>
    // Utilidad SHA-256 a hex
    async function sha256Hex(message){
        const enc=new TextEncoder();
        const data=enc.encode(message);
        const buf=await crypto.subtle.digest('SHA-256', data);
        return Array.from(new Uint8Array(buf)).map(b=>b.toString(16).padStart(2,'0')).join('');
    }

    async function renderQR(codigoQR, inscripcionId, eventoHash){
        const ts=Math.floor(Date.now()/1000);
        const hash=await sha256Hex(codigoQR+String(ts)+'eventaccess_salt');
        const payload={codigo_qr:codigoQR, inscripcion_id:inscripcionId, timestamp:ts, evento_hash:eventoHash, hash};
        const container=document.getElementById('qrcode');
        container.innerHTML='';
        if (typeof QRCode !== 'undefined') {
            try {
                new QRCode(container, {
                    text: JSON.stringify(payload),
                    width: 250,
                    height: 250,
                    colorDark: "#2c3e50",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (err) {
                container.innerHTML = '<div class="text-danger">Error generando QR</div>';
            }
        } else {
            container.innerHTML = '<div class="text-danger">No se pudo cargar la librería QR</div>';
        }
    }

    function startRefresh(codigoQR, inscripcionId, eventoHash){
        let timer=window.__qrTimer;
        if(timer){clearInterval(timer)}
        window.__qrTimer=setInterval(()=>renderQR(codigoQR, inscripcionId, eventoHash),7000);
    }

    // Esperar a que QRCode esté disponible antes de llamar a renderQR
    window.addEventListener('DOMContentLoaded', function() {
        if (typeof renderQRInit !== 'undefined') renderQRInit();
    });
</script>
</html>
