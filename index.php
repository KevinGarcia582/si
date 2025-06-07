<?php
require_once 'controllers/IncomeController.php';
require_once 'controllers/ExpenseController.php';
require_once 'controllers/CategoryController.php';
require_once 'controllers/ReportController.php';

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; script-src 'self' 'unsafe-inline';");


$controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : 'income';
$action = isset($_GET['action']) ? strtolower($_GET['action']) : 'index';

$allowedControllers = ['income', 'expense', 'category', 'report'];
if (!in_array($controller, $allowedControllers)) {
    $controller = 'income';
}

$incomeController = new IncomeController();
$expenseController = new ExpenseController();
$categoryController = new CategoryController();
$reportController = new ReportController();

$message_text = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
$message_type = 'info';

if ($message_text) {
    if (stripos($message_text, 'Error') !== false || stripos($message_text, 'no encontrado') !== false || stripos($message_text, 'inválido') !== false || stripos($message_text, 'permitidos') !== false) {
        $message_type = 'danger';
    } elseif (stripos($message_text, 'correctamente') !== false) {
        $message_type = 'success';
    }
}

if ($controller == 'report' && $action == 'view') {
    $month = isset($_GET['month']) ? htmlspecialchars($_GET['month']) : null;
    $year = isset($_GET['year']) ? filter_var($_GET['year'], FILTER_VALIDATE_INT) : null;

    if ($month && $year !== false) {
        $result = $reportController->generateMonthlyReport($month, $year);

        if ($result['success']) {
            $reportData = $result['data'];
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Reporte de <?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?></title>
                <link rel="stylesheet" href="views/css/styles.css">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

            </head>
            <body>
                <div class="report-container">
                    <div class="report-header print-only">
                         <h1>Reporte Financiero Mensual</h1>
                         <h2><?= htmlspecialchars($reportData['month'] ?? '', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($reportData['year'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <?php include 'views/report.php'; ?>

                     <div class="d-flex-custom justify-content-center-custom gap-3-custom mt-4-custom no-print">
                        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / Guardar PDF</button>
                        <a href="index.php?controller=report&action=form" class="btn btn-secondary"><i class="fas fa-arrow-circle-left"></i> Volver</a>
                    </div>

                </div>
                <script>
                    function printReport() {
                        window.print();
                    }
                     // Puedes llamar a printReport() directamente con onclick en el botón
                     // <button class="btn btn-primary" onclick="printReport()"><i class="fas fa-print"></i> Imprimir / Guardar PDF</button>
                </script>
            </body>
            </html>
            <?php
        } else {
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Error al Generar Reporte</title>
                <link rel="stylesheet" href="views/css/styles.css">
            </head>
            <body>
                <div class="error-container">
                    <div class="alert danger">
                        <?= htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <a href="index.php?controller=report&action=form" class="btn btn-primary">Volver al Formulario de Reporte</a>
                </div>
            </body>
            </html>
            <?php
        }
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error de Parámetros</title>
            <link rel="stylesheet" href="views/css/styles.css">
        </head>
        <body>
            <div class="error-container">
                <div class="alert danger">
                    Parámetros de mes o año inválidos para ver el reporte.
                </div>
                <a href="index.php?controller=report&action=form" class="btn btn-primary">Volver al Formulario de Reporte</a>
            </div>
        </body>
        </html>
        <?php
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Financiera</title>
    <link rel="stylesheet" href="views/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <header class="header no-print">
            <h1 class="text-center">Control Financiero</h1>
            <nav class="nav-tabs">
                <a href="index.php?controller=income" class="nav-link <?= $controller == 'income' ? 'active' : '' ?>">
                    <i class="fas fa-money-bill-wave"></i> Ingresos
                </a>
                <a href="index.php?controller=expense" class="nav-link <?= $controller == 'expense' ? 'active' : '' ?>">
                    <i class="fas fa-receipt"></i> Gastos
                </a>
                <a href="index.php?controller=category" class="nav-link <?= $controller == 'category' ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i> Categorías
                </a>
                <a href="index.php?controller=report&action=form" class="nav-link <?= $controller == 'report' ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Reportes
                </a>
            </nav>
        </header>

        <?php
        if ($message_text): ?>
            <div id="globalMessage" class="alert <?= $message_type ?> no-print" role="alert">
                <?= $message_text ?>
            </div>
        <?php endif; ?>

        <main class="content">
            <?php
            switch ($controller) {
                case 'income':
                    $incomes = $incomeController->getAllIncomes();
                    $isEditForm = false;
                    $income = null;

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        if ($action == 'register') {
                            $result = $incomeController->registerIncome(
                                $_POST['month'] ?? '',
                                $_POST['year'] ?? 0,
                                $_POST['value'] ?? 0.0
                            );
                             header('Location: index.php?controller=income&message='.urlencode($result['message']));
                             exit;
                        }
                        elseif ($action == 'update') {
                             $result = $incomeController->updateIncome(
                                $_POST['month'] ?? '',
                                $_POST['year'] ?? 0,
                                $_POST['value'] ?? 0.0
                            );
                            header('Location: index.php?controller=income&message='.urlencode($result['message']));
                            exit;
                        }
                    } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                        if ($action == 'edit') {
                             if (isset($_GET['month']) && isset($_GET['year'])) {
                                $isEditForm = true;
                                $income = $incomeController->getIncomeByMonthYear(
                                    htmlspecialchars($_GET['month']),
                                    (int)$_GET['year']
                                );
                                if (!$income) {
                                    header('Location: index.php?controller=income&message='.urlencode('Error: Ingreso a modificar no encontrado o parámetros inválidos.'));
                                    exit;
                                }
                            } else {
                                 header('Location: index.php?controller=income&message='.urlencode('Error: Faltan parámetros para modificar el ingreso.'));
                                exit;
                            }
                        }
                    }
                    include 'views/incomes.php';
                    break;

                case 'expense':
                    $categories = $expenseController->getAllCategories();
                    $expenses = $expenseController->getAllExpenses();
                    $expenseToEdit = null;

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        if ($action == 'register') {
                            $result = $expenseController->registerExpense(
                                $_POST['category'] ?? 0,
                                $_POST['month'] ?? '',
                                $_POST['year'] ?? 0,
                                $_POST['value'] ?? 0.0
                            );
                            header('Location: index.php?controller=expense&message='.urlencode($result['message']));
                            exit;
                        }
                        elseif ($action == 'update') {
                             $result = $expenseController->updateExpense(
                                $_POST['id'] ?? 0,
                                $_POST['category'] ?? 0,
                                $_POST['value'] ?? 0.0
                            );
                            header('Location: index.php?controller=expense&message='.urlencode($result['message']));
                            exit;
                        }
                    }
                    elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                         if ($action == 'delete' && isset($_GET['id'])) {
                            $result = $expenseController->deleteExpense($_GET['id']);
                            header('Location: index.php?controller=expense&message='.urlencode($result['message']));
                            exit;
                        }

                        if ($action == 'edit' && isset($_GET['id'])) {
                            $expenseToEdit = $expenseController->getExpenseById($_GET['id']);
                            if (!$expenseToEdit) {
                                header('Location: index.php?controller=expense&message='.urlencode('Gasto no encontrado o ID inválido.'));
                                exit;
                            }
                        }
                    }
                    include 'views/expense.php';
                    break;

                case 'category':
                    $category = null;

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                         if ($action == 'register') {
                            $result = $categoryController->registerCategory(
                                $_POST['name'] ?? '',
                                $_POST['percentage'] ?? ''
                            );
                            header('Location: index.php?controller=category&message='.urlencode($result['message']));
                            exit;
                        }
                        elseif ($action == 'update') {
                            $result = $categoryController->updateCategory(
                                $_POST['id'] ?? 0,
                                $_POST['name'] ?? '',
                                $_POST['percentage'] ?? ''
                            );
                            header('Location: index.php?controller=category&message='.urlencode($result['message']));
                            exit;
                        }
                    }
                    elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                        if ($action == 'delete' && isset($_GET['id'])) {
                            $result = $categoryController->deleteCategory($_GET['id']);
                            header('Location: index.php?controller=category&message='.urlencode($result['message']));
                            exit;
                        }

                        if ($action == 'edit' && isset($_GET['id'])) {
                            $category = $categoryController->getCategoryById($_GET['id']);
                            if (!$category) {
                                header('Location: index.php?controller=category&message='.urlencode('Error: Categoría no encontrada o ID inválido.'));
                                exit;
                            }
                        }
                    }
                    $categories = $categoryController->getAllCategories();
                    include 'views/categories.php';
                    break;

                case 'report':
                    switch ($action) {
                        case 'form':
                            include 'views/forms/report_form.php';
                            break;

                        case 'generate':
                            if (isset($_GET['month']) && isset($_GET['year'])) {
                                $month = htmlspecialchars($_GET['month']);
                                $year = filter_var($_GET['year'], FILTER_VALIDATE_INT);

                                if ($year === false || $year < 1900 || $year > 2100) {
                                     header('Location: index.php?controller=report&action=form&message='.urlencode('Error: Año inválido para el reporte.'));
                                     exit;
                                }

                                $result = $reportController->generateMonthlyReport($month, $year);

                                if ($result['success']) {
                                    $reportData = $result['data'];
                                    include 'views/report.php';
                                } else {
                                    $error = $result['message'];
                                    include 'views/report.php';
                                }
                            } else {
                                header('Location: index.php?controller=report&action=form&message='.urlencode('Error: Mes y año son requeridos para generar el reporte.'));
                                exit;
                            }
                            break;

                        default:
                            header('Location: index.php?controller=report&action=form');
                            exit;
                    }
                    break;

                default:
                    header('Location: index.php?controller=income');
                    exit;
            }
            ?>
        </main>

        <div class="quick-actions fixed-bottom mb-4 text-center no-print">
            <div class="btn-group" role="group">
                <a href="index.php?controller=income&action=register" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nuevo Ingreso
                </a>
                 <a href="index.php?controller=expense&action=register" class="btn btn-secondary">
                    <i class="fas fa-minus-circle"></i> Nuevo Gasto
                </a>
                <a href="index.php?controller=category&action=register" class="btn btn-info">
                    <i class="fas fa-tag"></i> Nueva Categoría
                </a>
                <a href="index.php?controller=report&action=form" class="btn btn-warning">
                    <i class="fas fa-file-alt"></i> Generar Reporte
                </a>
            </div>
        </div>
    </div>

</body>
</html>