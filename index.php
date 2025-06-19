<?php
// Configuración de la base de datos SQLite
$db = new SQLite3('tienda_zapatos.db');

// Crear tablas si no existen
initializeDatabase($db);

// Manejo de acciones
$action = $_GET['action'] ?? 'dashboard';
$section = $_GET['section'] ?? 'dashboard';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostRequest($db);
}

// Mostrar la interfaz
renderInterface($db, $section, $action);

// Funciones de la aplicación
function initializeDatabase($db) {
    // Tabla de productos
    $db->exec('CREATE TABLE IF NOT EXISTS productos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        descripcion TEXT,
        color TEXT,
        talla TEXT,
        precio REAL NOT NULL,
        stock INTEGER NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');

    // Tabla de clientes
    $db->exec('CREATE TABLE IF NOT EXISTS clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        direccion TEXT,
        telefono TEXT,
        email TEXT,
        rfc TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');

    // Tabla de órdenes
    $db->exec('CREATE TABLE IF NOT EXISTS ordenes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cliente_id INTEGER,
        fecha TEXT NOT NULL,
        hora TEXT NOT NULL,
        subtotal REAL NOT NULL,
        iva REAL NOT NULL,
        total REAL NOT NULL,
        metodo_pago TEXT,
        tarjeta TEXT,
        cajero TEXT,
        caja TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
    )');

    // Tabla de items de orden
    $db->exec('CREATE TABLE IF NOT EXISTS orden_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        orden_id INTEGER NOT NULL,
        producto_id INTEGER NOT NULL,
        cantidad INTEGER NOT NULL,
        precio_unitario REAL NOT NULL,
        total REAL NOT NULL,
        FOREIGN KEY (orden_id) REFERENCES ordenes(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id)
    )');
}

function handlePostRequest($db) {
    $section = $_POST['section'] ?? '';
    
    switch ($section) {
        case 'productos':
            handleProductosPost($db);
            break;
        case 'clientes':
            handleClientesPost($db);
            break;
        case 'ordenes':
            handleOrdenesPost($db);
            break;
    }
}

function handleProductosPost($db) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $color = $_POST['color'] ?? '';
        $talla = $_POST['talla'] ?? '';
        $precio = floatval($_POST['precio'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        
        $stmt = $db->prepare('INSERT INTO productos (nombre, descripcion, color, talla, precio, stock) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bindValue(1, $nombre, SQLITE3_TEXT);
        $stmt->bindValue(2, $descripcion, SQLITE3_TEXT);
        $stmt->bindValue(3, $color, SQLITE3_TEXT);
        $stmt->bindValue(4, $talla, SQLITE3_TEXT);
        $stmt->bindValue(5, $precio, SQLITE3_FLOAT);
        $stmt->bindValue(6, $stock, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($action === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $color = $_POST['color'] ?? '';
        $talla = $_POST['talla'] ?? '';
        $precio = floatval($_POST['precio'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        
        $stmt = $db->prepare('UPDATE productos SET nombre = ?, descripcion = ?, color = ?, talla = ?, precio = ?, stock = ? WHERE id = ?');
        $stmt->bindValue(1, $nombre, SQLITE3_TEXT);
        $stmt->bindValue(2, $descripcion, SQLITE3_TEXT);
        $stmt->bindValue(3, $color, SQLITE3_TEXT);
        $stmt->bindValue(4, $talla, SQLITE3_TEXT);
        $stmt->bindValue(5, $precio, SQLITE3_FLOAT);
        $stmt->bindValue(6, $stock, SQLITE3_INTEGER);
        $stmt->bindValue(7, $id, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($action === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $db->prepare('DELETE FROM productos WHERE id = ?');
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

function handleClientesPost($db) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $nombre = $_POST['nombre'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';
        $rfc = $_POST['rfc'] ?? '';
        
        $stmt = $db->prepare('INSERT INTO clientes (nombre, direccion, telefono, email, rfc) VALUES (?, ?, ?, ?, ?)');
        $stmt->bindValue(1, $nombre, SQLITE3_TEXT);
        $stmt->bindValue(2, $direccion, SQLITE3_TEXT);
        $stmt->bindValue(3, $telefono, SQLITE3_TEXT);
        $stmt->bindValue(4, $email, SQLITE3_TEXT);
        $stmt->bindValue(5, $rfc, SQLITE3_TEXT);
        $stmt->execute();
    } elseif ($action === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = $_POST['nombre'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';
        $rfc = $_POST['rfc'] ?? '';
        
        $stmt = $db->prepare('UPDATE clientes SET nombre = ?, direccion = ?, telefono = ?, email = ?, rfc = ? WHERE id = ?');
        $stmt->bindValue(1, $nombre, SQLITE3_TEXT);
        $stmt->bindValue(2, $direccion, SQLITE3_TEXT);
        $stmt->bindValue(3, $telefono, SQLITE3_TEXT);
        $stmt->bindValue(4, $email, SQLITE3_TEXT);
        $stmt->bindValue(5, $rfc, SQLITE3_TEXT);
        $stmt->bindValue(6, $id, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($action === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $db->prepare('DELETE FROM clientes WHERE id = ?');
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

function handleOrdenesPost($db) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $cliente_id = intval($_POST['cliente_id'] ?? 0);
        $fecha = date('d/M/Y');
        $hora = date('H:i:s');
        $metodo_pago = $_POST['metodo_pago'] ?? 'Efectivo';
        $tarjeta = $_POST['tarjeta'] ?? '';
        $cajero = $_POST['cajero'] ?? 'María González';
        $caja = $_POST['caja'] ?? '03';
        
        // Calcular totales
        $subtotal = 0;
        $items = $_POST['items'] ?? [];
        
        foreach ($items as $item) {
            $producto_id = intval($item['producto_id'] ?? 0);
            $cantidad = intval($item['cantidad'] ?? 0);
            
            $producto = $db->querySingle("SELECT precio FROM productos WHERE id = $producto_id", true);
            $precio = floatval($producto['precio'] ?? 0);
            
            $subtotal += $precio * $cantidad;
        }
        
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;
        
        // Insertar orden
        $stmt = $db->prepare('INSERT INTO ordenes (cliente_id, fecha, hora, subtotal, iva, total, metodo_pago, tarjeta, cajero, caja) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bindValue(1, $cliente_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $fecha, SQLITE3_TEXT);
        $stmt->bindValue(3, $hora, SQLITE3_TEXT);
        $stmt->bindValue(4, $subtotal, SQLITE3_FLOAT);
        $stmt->bindValue(5, $iva, SQLITE3_FLOAT);
        $stmt->bindValue(6, $total, SQLITE3_FLOAT);
        $stmt->bindValue(7, $metodo_pago, SQLITE3_TEXT);
        $stmt->bindValue(8, $tarjeta, SQLITE3_TEXT);
        $stmt->bindValue(9, $cajero, SQLITE3_TEXT);
        $stmt->bindValue(10, $caja, SQLITE3_TEXT);
        $stmt->execute();
        
        $orden_id = $db->lastInsertRowID();
        
        // Insertar items
        foreach ($items as $item) {
            $producto_id = intval($item['producto_id'] ?? 0);
            $cantidad = intval($item['cantidad'] ?? 0);
            
            $producto = $db->querySingle("SELECT precio FROM productos WHERE id = $producto_id", true);
            $precio = floatval($producto['precio'] ?? 0);
            $total_item = $precio * $cantidad;
            
            $stmt = $db->prepare('INSERT INTO orden_items (orden_id, producto_id, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)');
            $stmt->bindValue(1, $orden_id, SQLITE3_INTEGER);
            $stmt->bindValue(2, $producto_id, SQLITE3_INTEGER);
            $stmt->bindValue(3, $cantidad, SQLITE3_INTEGER);
            $stmt->bindValue(4, $precio, SQLITE3_FLOAT);
            $stmt->bindValue(5, $total_item, SQLITE3_FLOAT);
            $stmt->execute();
            
            // Actualizar stock
            $db->exec("UPDATE productos SET stock = stock - $cantidad WHERE id = $producto_id");
        }
        
        // Redirigir a la vista del ticket
        header("Location: ?section=ordenes&action=ticket&id=$orden_id");
        exit();
    }
}

function renderInterface($db, $section, $action) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daniel's Shoes Store - Sistema de Gestión</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="container">
            <header>
                <h1>Daniel's Shoes Store</h1>
                <p>Sistema de Gestión</p>
            </header>
            
            <nav>
                <ul>
                    <li><a href="?section=dashboard">Dashboard</a></li>
                    <li><a href="?section=productos">Productos</a></li>
                    <li><a href="?section=clientes">Clientes</a></li>
                    <li><a href="?section=ordenes">Órdenes</a></li>
                </ul>
            </nav>
            
            <main>
                <?php
                switch ($section) {
                    case 'dashboard':
                        renderDashboard($db);
                        break;
                    case 'productos':
                        renderProductos($db, $action);
                        break;
                    case 'clientes':
                        renderClientes($db, $action);
                        break;
                    case 'ordenes':
                        renderOrdenes($db, $action);
                        break;
                    default:
                        renderDashboard($db);
                }
                ?>
            </main>
            
            <footer>
                <p>Sistema de Gestión para Daniel's Shoes Store &copy; <?= date('Y') ?></p>
            </footer>
        </div>
    </body>
    </html>
    <?php
}

function renderDashboard($db) {
    // Obtener estadísticas
    $total_productos = $db->querySingle("SELECT COUNT(*) FROM productos");
    $total_clientes = $db->querySingle("SELECT COUNT(*) FROM clientes");
    $total_ordenes = $db->querySingle("SELECT COUNT(*) FROM ordenes");
    $ventas_hoy = $db->querySingle("SELECT COUNT(*) FROM ordenes WHERE date(fecha) = date('now')");
    
    // Obtener últimas órdenes
    $ordenes = $db->query("SELECT o.id, o.fecha, o.total, c.nombre as cliente FROM ordenes o LEFT JOIN clientes c ON o.cliente_id = c.id ORDER BY o.id DESC LIMIT 5");
    
    // Obtener productos con bajo stock
    $bajo_stock = $db->query("SELECT nombre, stock FROM productos WHERE stock < 5 ORDER BY stock ASC LIMIT 5");
    ?>
    <section class="dashboard">
        <h2>Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Productos</h3>
                <p><?= $total_productos ?></p>
            </div>
            <div class="stat-card">
                <h3>Clientes</h3>
                <p><?= $total_clientes ?></p>
            </div>
            <div class="stat-card">
                <h3>Órdenes</h3>
                <p><?= $total_ordenes ?></p>
            </div>
            <div class="stat-card">
                <h3>Ventas Hoy</h3>
                <p><?= $ventas_hoy ?></p>
            </div>
        </div>
        
        <div class="dashboard-sections">
            <div class="dashboard-section">
                <h3>Últimas Órdenes</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                    </tr>
                    <?php while ($orden = $ordenes->fetchArray()): ?>
                    <tr>
                        <td><a href="?section=ordenes&action=ver&id=<?= $orden['id'] ?>">#<?= $orden['id'] ?></a></td>
                        <td><?= $orden['fecha'] ?></td>
                        <td><?= $orden['cliente'] ?? 'Sin cliente' ?></td>
                        <td>$<?= number_format($orden['total'], 2) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            
            <div class="dashboard-section">
                <h3>Productos con Bajo Stock</h3>
                <table>
                    <tr>
                        <th>Producto</th>
                        <th>Stock</th>
                    </tr>
                    <?php while ($producto = $bajo_stock->fetchArray()): ?>
                    <tr>
                        <td><?= $producto['nombre'] ?></td>
                        <td class="<?= $producto['stock'] < 3 ? 'text-danger' : 'text-warning' ?>"><?= $producto['stock'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </section>
    <?php
}

function renderProductos($db, $action) {
    if ($action === 'crear' || $action === 'editar') {
        $id = $_GET['id'] ?? 0;
        $producto = [];
        
        if ($action === 'editar' && $id) {
            $producto = $db->querySingle("SELECT * FROM productos WHERE id = $id", true);
        }
        ?>
        <section class="productos-form">
            <h2><?= $action === 'crear' ? 'Agregar Producto' : 'Editar Producto' ?></h2>
            
            <form method="post">
                <input type="hidden" name="section" value="productos">
                <input type="hidden" name="action" value="<?= $action ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= $producto['nombre'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion"><?= $producto['descripcion'] ?? '' ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="color">Color:</label>
                        <input type="text" id="color" name="color" value="<?= $producto['color'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="talla">Talla:</label>
                        <input type="text" id="talla" name="talla" value="<?= $producto['talla'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?= $producto['precio'] ?? '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock:</label>
                        <input type="number" id="stock" name="stock" min="0" value="<?= $producto['stock'] ?? 0 ?>" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="?section=productos" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
        <?php
    } else {
        $productos = $db->query("SELECT * FROM productos ORDER BY nombre");
        ?>
        <section class="productos-list">
            <div class="section-header">
                <h2>Productos</h2>
                <a href="?section=productos&action=crear" class="btn btn-primary">Agregar Producto</a>
            </div>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Color/Talla</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($producto = $productos->fetchArray()): ?>
                <tr>
                    <td><?= $producto['id'] ?></td>
                    <td><?= $producto['nombre'] ?></td>
                    <td><?= $producto['color'] ?>/<?= $producto['talla'] ?></td>
                    <td>$<?= number_format($producto['precio'], 2) ?></td>
                    <td class="<?= $producto['stock'] < 3 ? 'text-danger' : ($producto['stock'] < 10 ? 'text-warning' : '') ?>">
                        <?= $producto['stock'] ?>
                    </td>
                    <td class="actions">
                        <a href="?section=productos&action=editar&id=<?= $producto['id'] ?>" class="btn btn-sm btn-edit">Editar</a>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="section" value="productos">
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este producto?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>
        <?php
    }
}

function renderClientes($db, $action) {
    if ($action === 'crear' || $action === 'editar') {
        $id = $_GET['id'] ?? 0;
        $cliente = [];
        
        if ($action === 'editar' && $id) {
            $cliente = $db->querySingle("SELECT * FROM clientes WHERE id = $id", true);
        }
        ?>
        <section class="clientes-form">
            <h2><?= $action === 'crear' ? 'Agregar Cliente' : 'Editar Cliente' ?></h2>
            
            <form method="post">
                <input type="hidden" name="section" value="clientes">
                <input type="hidden" name="action" value="<?= $action ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= $cliente['nombre'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" value="<?= $cliente['direccion'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" value="<?= $cliente['telefono'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= $cliente['email'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="rfc">RFC:</label>
                    <input type="text" id="rfc" name="rfc" value="<?= $cliente['rfc'] ?? '' ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="?section=clientes" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
        <?php
    } else {
        $clientes = $db->query("SELECT * FROM clientes ORDER BY nombre");
        ?>
        <section class="clientes-list">
            <div class="section-header">
                <h2>Clientes</h2>
                <a href="?section=clientes&action=crear" class="btn btn-primary">Agregar Cliente</a>
            </div>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>RFC</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($cliente = $clientes->fetchArray()): ?>
                <tr>
                    <td><?= $cliente['id'] ?></td>
                    <td><?= $cliente['nombre'] ?></td>
                    <td><?= $cliente['telefono'] ?></td>
                    <td><?= $cliente['email'] ?></td>
                    <td><?= $cliente['rfc'] ?></td>
                    <td class="actions">
                        <a href="?section=clientes&action=editar&id=<?= $cliente['id'] ?>" class="btn btn-sm btn-edit">Editar</a>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="section" value="clientes">
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="id" value="<?= $cliente['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este cliente?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>
        <?php
    }
}

function renderOrdenes($db, $action) {
    if ($action === 'crear') {
        $productos = $db->query("SELECT * FROM productos ORDER BY nombre");
        $clientes = $db->query("SELECT * FROM clientes ORDER BY nombre");
        ?>
        <section class="ordenes-form">
            <h2>Nueva Orden</h2>
            
            <form method="post" id="orden-form">
                <input type="hidden" name="section" value="ordenes">
                <input type="hidden" name="action" value="crear">
                
                <div class="form-group">
                    <label for="cliente_id">Cliente:</label>
                    <select id="cliente_id" name="cliente_id">
                        <option value="0">-- Seleccionar Cliente --</option>
                        <?php while ($cliente = $clientes->fetchArray()): ?>
                        <option value="<?= $cliente['id'] ?>"><?= $cliente['nombre'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cajero">Cajero:</label>
                        <input type="text" id="cajero" name="cajero" value="María González">
                    </div>
                    
                    <div class="form-group">
                        <label for="caja">Caja #:</label>
                        <input type="text" id="caja" name="caja" value="03">
                    </div>
                </div>
                
                <h3>Productos</h3>
                <div id="productos-container">
                    <div class="producto-item">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Producto:</label>
                                <select name="items[0][producto_id]" class="producto-select" required>
                                    <option value="">-- Seleccionar Producto --</option>
                                    <?php while ($producto = $productos->fetchArray()): ?>
                                    <option value="<?= $producto['id'] ?>" data-precio="<?= $producto['precio'] ?>">
                                        <?= $producto['nombre'] ?> - $<?= number_format($producto['precio'], 2) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Cantidad:</label>
                                <input type="number" name="items[0][cantidad]" min="1" value="1" class="cantidad-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Total:</label>
                                <span class="producto-total">$0.00</span>
                            </div>
                            
                            <button type="button" class="btn btn-danger btn-remove-producto">Eliminar</button>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="btn-add-producto" class="btn btn-secondary">Agregar Producto</button>
                
                <div class="totales-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Método de Pago:</label>
                            <select name="metodo_pago">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta Visa">Tarjeta Visa</option>
                                <option value="Tarjeta Mastercard">Tarjeta Mastercard</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Últimos 4 dígitos (si aplica):</label>
                            <input type="text" name="tarjeta" placeholder="1234" maxlength="4">
                        </div>
                    </div>
                    
                    <div class="totales">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="total-row">
                            <span>IVA (16%):</span>
                            <span id="iva">$0.00</span>
                        </div>
                        <div class="total-row">
                            <strong>Total:</strong>
                            <strong id="total">$0.00</strong>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Orden</button>
                    <a href="?section=ordenes" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Agregar nuevo producto
                let productoIndex = 1;
                document.getElementById('btn-add-producto').addEventListener('click', function() {
                    const container = document.getElementById('productos-container');
                    const newItem = document.createElement('div');
                    newItem.className = 'producto-item';
                    newItem.innerHTML = `
                        <div class="form-row">
                            <div class="form-group">
                                <label>Producto:</label>
                                <select name="items[${productoIndex}][producto_id]" class="producto-select" required>
                                    <option value="">-- Seleccionar Producto --</option>
                                    <?php 
                                    $productos->reset(); 
                                    while ($producto = $productos->fetchArray()): ?>
                                    <option value="<?= $producto['id'] ?>" data-precio="<?= $producto['precio'] ?>">
                                        <?= $producto['nombre'] ?> - $<?= number_format($producto['precio'], 2) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Cantidad:</label>
                                <input type="number" name="items[${productoIndex}][cantidad]" min="1" value="1" class="cantidad-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Total:</label>
                                <span class="producto-total">$0.00</span>
                            </div>
                            
                            <button type="button" class="btn btn-danger btn-remove-producto">Eliminar</button>
                        </div>
                    `;
                    container.appendChild(newItem);
                    productoIndex++;
                    
                    // Agregar eventos al nuevo producto
                    addProductoEvents(newItem);
                });
                
                // Eliminar producto
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('btn-remove-producto')) {
                        if (document.querySelectorAll('.producto-item').length > 1) {
                            e.target.closest('.producto-item').remove();
                            calcularTotales();
                        } else {
                            alert('Debe haber al menos un producto en la orden.');
                        }
                    }
                });
                
                // Calcular totales cuando cambian los productos o cantidades
                function addProductoEvents(item) {
                    const select = item.querySelector('.producto-select');
                    const cantidad = item.querySelector('.cantidad-input');
                    const totalSpan = item.querySelector('.producto-total');
                    
                    select.addEventListener('change', calcularTotales);
                    cantidad.addEventListener('input', calcularTotales);
                }
                
                // Agregar eventos a los productos iniciales
                document.querySelectorAll('.producto-item').forEach(item => {
                    addProductoEvents(item);
                });
                
                // Función para calcular totales
                function calcularTotales() {
                    let subtotal = 0;
                    
                    document.querySelectorAll('.producto-item').forEach(item => {
                        const select = item.querySelector('.producto-select');
                        const cantidad = item.querySelector('.cantidad-input');
                        const totalSpan = item.querySelector('.producto-total');
                        
                        if (select.value && cantidad.value) {
                            const precio = parseFloat(select.selectedOptions[0].dataset.precio);
                            const cant = parseInt(cantidad.value);
                            const total = precio * cant;
                            
                            totalSpan.textContent = '$' + total.toFixed(2);
                            subtotal += total;
                        }
                    });
                    
                    const iva = subtotal * 0.16;
                    const total = subtotal + iva;
                    
                    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
                    document.getElementById('iva').textContent = '$' + iva.toFixed(2);
                    document.getElementById('total').textContent = '$' + total.toFixed(2);
                }
            });
        </script>
        <?php
    } elseif ($action === 'ticket') {
        $id = $_GET['id'] ?? 0;
        if ($id) {
            renderTicket($db, $id);
        } else {
            echo '<p>No se especificó una orden.</p>';
        }
    } elseif ($action === 'ver') {
        $id = $_GET['id'] ?? 0;
        if ($id) {
            renderVerOrden($db, $id);
        } else {
            echo '<p>No se especificó una orden.</p>';
        }
    } else {
        $ordenes = $db->query("SELECT o.id, o.fecha, o.total, c.nombre as cliente FROM ordenes o LEFT JOIN clientes c ON o.cliente_id = c.id ORDER BY o.id DESC");
        ?>
        <section class="ordenes-list">
            <div class="section-header">
                <h2>Órdenes</h2>
                <a href="?section=ordenes&action=crear" class="btn btn-primary">Nueva Orden</a>
            </div>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($orden = $ordenes->fetchArray()): ?>
                <tr>
                    <td><?= $orden['id'] ?></td>
                    <td><?= $orden['fecha'] ?></td>
                    <td><?= $orden['cliente'] ?? 'Sin cliente' ?></td>
                    <td>$<?= number_format($orden['total'], 2) ?></td>
                    <td class="actions">
                        <a href="?section=ordenes&action=ticket&id=<?= $orden['id'] ?>" class="btn btn-sm btn-primary" target="_blank">Ticket</a>
                        <a href="?section=ordenes&action=ver&id=<?= $orden['id'] ?>" class="btn btn-sm btn-edit">Ver</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>
        <?php
    }
}

function renderVerOrden($db, $id) {
    $orden = $db->querySingle("SELECT o.*, c.nombre as cliente_nombre, c.rfc as cliente_rfc 
                             FROM ordenes o LEFT JOIN clientes c ON o.cliente_id = c.id 
                             WHERE o.id = $id", true);
    
    if (!$orden) {
        echo '<p>Orden no encontrada.</p>';
        return;
    }
    
    $items = $db->query("SELECT oi.*, p.nombre as producto_nombre, p.color, p.talla 
                        FROM orden_items oi JOIN productos p ON oi.producto_id = p.id 
                        WHERE oi.orden_id = $id");
    ?>
    <section class="orden-detalle">
        <div class="section-header">
            <h2>Orden #<?= $orden['id'] ?></h2>
            <a href="?section=ordenes&action=ticket&id=<?= $orden['id'] ?>" class="btn btn-primary" target="_blank">Ver Ticket</a>
            <a href="?section=ordenes" class="btn btn-secondary">Volver</a>
        </div>
        
        <div class="orden-info">
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value"><?= $orden['fecha'] ?> <?= $orden['hora'] ?></span>
            </div>
            
            <?php if ($orden['cliente_nombre']): ?>
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value"><?= $orden['cliente_nombre'] ?></span>
            </div>
            
            <?php if ($orden['cliente_rfc']): ?>
            <div class="info-row">
                <span class="info-label">RFC:</span>
                <span class="info-value"><?= $orden['cliente_rfc'] ?></span>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <div class="info-row">
                <span class="info-label">Cajero:</span>
                <span class="info-value"><?= $orden['cajero'] ?> (Caja #<?= $orden['caja'] ?>)</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Método de Pago:</span>
                <span class="info-value"><?= $orden['metodo_pago'] ?></span>
            </div>
            
            <?php if ($orden['tarjeta']): ?>
            <div class="info-row">
                <span class="info-label">Tarjeta:</span>
                <span class="info-value">****<?= $orden['tarjeta'] ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <h3>Productos</h3>
        <table>
            <tr>
                <th>Producto</th>
                <th>Color/Talla</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
            <?php while ($item = $items->fetchArray()): ?>
            <tr>
                <td><?= $item['producto_nombre'] ?></td>
                <td><?= $item['color'] ?>/<?= $item['talla'] ?></td>
                <td><?= $item['cantidad'] ?></td>
                <td>$<?= number_format($item['precio_unitario'], 2) ?></td>
                <td>$<?= number_format($item['total'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        
        <div class="orden-totales">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>$<?= number_format($orden['subtotal'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>IVA (16%):</span>
                <span>$<?= number_format($orden['iva'], 2) ?></span>
            </div>
            <div class="total-row">
                <strong>Total:</strong>
                <strong>$<?= number_format($orden['total'], 2) ?></strong>
            </div>
        </div>
    </section>
    <?php
}

function renderTicket($db, $id) {
    $orden = $db->querySingle("SELECT o.*, c.nombre as cliente_nombre, c.rfc as cliente_rfc 
                             FROM ordenes o LEFT JOIN clientes c ON o.cliente_id = c.id 
                             WHERE o.id = $id", true);
    
    if (!$orden) {
        echo '<p>Orden no encontrada.</p>';
        return;
    }
    
    $items = $db->query("SELECT oi.*, p.nombre as producto_nombre, p.color, p.talla 
                        FROM orden_items oi JOIN productos p ON oi.producto_id = p.id 
                        WHERE oi.orden_id = $id");
    
    // Preparar el contenido del ticket
    $ticketContent = "";
    
    // Encabezado
    $ticketContent .= str_repeat("=", 43) . "\n";
    $ticketContent .= str_pad("DANIEL'S SHOES STORE", 43, " ", STR_PAD_BOTH) . "\n";
    $ticketContent .= str_pad("Calle Principal 123, Ciudad MX", 43, " ", STR_PAD_BOTH) . "\n";
    $ticketContent .= str_pad("Tel: 555-123-4567", 43, " ", STR_PAD_BOTH) . "\n";
    $ticketContent .= str_pad("RFC: DAN950201AB1", 43, " ", STR_PAD_BOTH) . "\n";
    $ticketContent .= str_repeat("=", 43) . "\n";
    
    // Información del ticket
    $ticketContent .= str_pad("TICKET DE VENTA #" . str_pad($orden['id'], 5, "0", STR_PAD_LEFT), 43, " ", STR_PAD_BOTH) . "\n";
    $ticketContent .= "Fecha: " . $orden['fecha'] . str_repeat(" ", 15 - strlen($orden['fecha'])) . "Hora: " . $orden['hora'] . "\n";
    $ticketContent .= str_repeat("=", 43) . "\n";
    $ticketContent .= "Cajero: " . $orden['cajero'] . str_repeat(" ", 20 - strlen($orden['cajero'])) . "Caja #" . $orden['caja'] . "\n";
    $ticketContent .= str_repeat("-", 43) . "\n";
    
    // Encabezado de artículos
    $ticketContent .= "ARTÍCULO" . str_repeat(" ", 18) . "CANT  PRECIO  TOTAL\n";
    $ticketContent .= str_repeat("-", 43) . "\n";
    
    // Items
    while ($item = $items->fetchArray()) {
        $nombre = substr($item['producto_nombre'], 0, 22); // Limitar a 22 caracteres
        $ticketContent .= $nombre . str_repeat(" ", 24 - strlen($nombre));
        $ticketContent .= str_pad($item['cantidad'], 3, " ", STR_PAD_LEFT) . "  ";
        $ticketContent .= "$" . number_format($item['precio_unitario'], 2) . "  ";
        $ticketContent .= "$" . number_format($item['total'], 2) . "\n";
        
        // Detalles de color y talla
        if (!empty($item['color']) || !empty($item['talla'])) {
            $ticketContent .= "Color: " . $item['color'] . str_repeat(" ", 36 - strlen("Color: " . $item['color']));
            $ticketContent .= "Talla: " . $item['talla'] . "\n";
        }
        
        $ticketContent .= "\n"; // Espacio entre productos
    }
    
    $ticketContent .= str_repeat("-", 43) . "\n";
    
    // Totales
    $ticketContent .= "SUBTOTAL:" . str_repeat(" ", 26) . "$" . number_format($orden['subtotal'], 2) . "\n";
    $ticketContent .= "IVA (16%):" . str_repeat(" ", 26) . "$" . number_format($orden['iva'], 2) . "\n";
    $ticketContent .= "TOTAL:" . str_repeat(" ", 30) . "$" . number_format($orden['total'], 2) . "\n";
    $ticketContent .= str_repeat("-", 43) . "\n";
    
    // Método de pago
    $ticketContent .= "FORMA DE PAGO: " . $orden['metodo_pago'] . "\n";
    if ($orden['tarjeta']) {
        $ticketContent .= "ÚLT. 4 DÍGITOS: ****" . $orden['tarjeta'] . "\n";
    }
    $ticketContent .= str_repeat("-", 43) . "\n";
    
    // Pie de página
    $ticketContent .= str_pad("¡GRACIAS POR SU COMPRA!", 43, " ", STR_PAD_BOTH) . "\n";
    $ticketContent .= str_pad("*Devoluciones en 15 días con ticket*", 43, " ", STR_PAD_BOTH) . "\n";
    $ticketContent .= str_repeat("=", 43) . "\n";
    
    // Mostrar el ticket en un formato imprimible
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ticket de Venta #<?= $orden['id'] ?></title>
        <style>
            body {
                background-color: #f5f5f5;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .ticket-container {
                background-color: white;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                max-width: 100%;
            }
            .ticket-content {
                font-family: 'Courier New', monospace;
                white-space: pre;
                line-height: 1.2;
                font-size: 14px;
            }
            .ticket-actions {
                margin-top: 20px;
                text-align: center;
            }
            .print-button {
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 10px 20px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                cursor: pointer;
                border-radius: 4px;
            }
            .back-button {
                background-color: #f44336;
                color: white;
                border: none;
                padding: 10px 20px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                cursor: pointer;
                border-radius: 4px;
            }
            @media print {
                body {
                    background-color: white;
                    margin: 0;
                    padding: 0;
                    display: block;
                }
                .ticket-container {
                    box-shadow: none;
                    padding: 0;
                    margin: 0;
                    width: 100%;
                    max-width: none;
                }
                .ticket-content {
                    font-size: 16px;
                }
                .ticket-actions {
                    display: none;
                }
            }

        </style>
    </head>
    <body>
        <div class="ticket-container">
            <div class="ticket-content"><?= htmlspecialchars($ticketContent) ?></div>
            <div class="ticket-actions">
                <button onclick="window.print()" class="print-button">Imprimir Ticket</button>
                <a href="?section=ordenes&action=ver&id=<?= $orden['id'] ?>" class="back-button">Volver</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>