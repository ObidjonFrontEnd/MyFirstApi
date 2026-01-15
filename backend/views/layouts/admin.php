<?php
use yii\helpers\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use backend\assets\AppAsset;

AppAsset::register($this);

$this->beginPage();
?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?> - Admin Panel</title>
        <?php $this->head() ?>
        <style>
            body {
                min-height: 100vh;
            }
            .sidebar {
                min-height: calc(100vh - 56px);
                background-color: #f8f9fa;
                border-right: 1px solid #dee2e6;
                padding-top: 20px;
            }
            .sidebar .nav-link {
                color: #333;
                padding: 10px 20px;
                border-radius: 0;
            }
            .sidebar .nav-link:hover,
            .sidebar .nav-link.active {
                background-color: #e9ecef;
                color: #0d6efd;
            }
            .main-content {
                padding: 20px;
            }
            @media (max-width: 768px) {
                .sidebar {
                    min-height: auto;
                }
            }
        </style>
    </head>
    <body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <header>
        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->name . ' - Admin Panel',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar navbar-expand-md navbar-dark bg-dark',
            ],
        ]);

        $menuItems = [
            ['label' => 'Home', 'url' => ['/site/index']],
        ];

        if (Yii::$app->user->isGuest) {
            $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
        } else {
            $menuItems[] = '<li class="nav-item">'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link nav-link logout text-decoration-none']
                )
                . Html::endForm()
                . '</li>';
        }

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav ms-auto'],
            'items' => $menuItems,
        ]);

        NavBar::end();
        ?>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Left Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>RBAC Management</span>
                    </h6>
                    <?php
                    echo Nav::widget([
                        'options' => ['class' => 'nav flex-column'],
                        'items' => [
                            [
                                'label' => '<i class="bi bi-diagram-3"></i> Routes',
                                'url' => ['/admin/route'],
                                'encode' => false,
                            ],
                            [
                                'label' => '<i class="bi bi-shield-check"></i> Permissions',
                                'url' => ['/admin/permission'],
                                'encode' => false,
                            ],
                            [
                                'label' => '<i class="bi bi-person-badge"></i> Roles',
                                'url' => ['/admin/role'],
                                'encode' => false,
                            ],
                            [
                                'label' => '<i class="bi bi-people"></i> Assignments',
                                'url' => ['/admin/assignment'],
                                'encode' => false,
                            ],
                            [
                                'label' => '<i class="bi bi-list"></i> Menu',
                                'url' => ['/admin/menu'],
                                'encode' => false,
                            ],
                        ],
                    ]);
                    ?>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Other</span>
                    </h6>
                    <?php
                    echo Nav::widget([
                        'options' => ['class' => 'nav flex-column'],
                        'items' => [
                            [
                                'label' => '<i class="bi bi-house"></i> Dashboard',
                                'url' => ['/site/index'],
                                'encode' => false,
                            ],
                            // Добавьте здесь другие пункты меню
                        ],
                    ]);
                    ?>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <?= $content ?>
            </main>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted mb-0"><?= Yii::powered() ?></p>
                </div>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>