<?php

if (!function_exists('render_main_nav')) {
    function render_main_nav(string $active = ''): void
    {
        $linkClass = static function (string $key, string $activeKey): string {
            return $key === $activeKey ? 'text-red-400' : 'hover:text-red-400 transition';
        };

        $isLoggedIn = isset($_SESSION['user_id']);
        $displayName = $_SESSION['user_name'] ?? 'Conta';
        $displayEmail = $_SESSION['user_email'] ?? '';
        $displayPic = $_SESSION['user_profile_pic'] ?? '';
        $initial = strtoupper(substr(trim((string) $displayName) ?: 'U', 0, 1));
        ?>
        <nav class="fixed top-0 w-full z-40 backdrop-blur border-b border-neutral-700">
            <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                <a href="index.php" class="flex items-center gap-3">
                    <img src="assets/logo-mma360.png.png" class="h-12 md:h-14" alt="Logo MMA 360">
                    <span class="text-xl font-semibold tracking-widest text-red-500">MMA 360</span>
                </a>

                <ul class="hidden md:flex gap-8 text-sm uppercase tracking-wide">
                    <li><a href="index.php" class="<?= $linkClass('index', $active) ?>">Início</a></li>
                    <li><a href="noticias.php" class="<?= $linkClass('noticias', $active) ?>">Notícias</a></li>
                    <li><a href="about.php" class="<?= $linkClass('about', $active) ?>">Quem Somos</a></li>
                    <li><a href="fighters.php" class="<?= $linkClass('fighters', $active) ?>">Lutadores</a></li>
                    <li><a href="eventos.php" class="<?= $linkClass('eventos', $active) ?>">Eventos</a></li>
                    <li><a href="contacto.php" class="<?= $linkClass('contacto', $active) ?>">Contacto</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li class="relative account-menu">
                            <button type="button" class="account-menu-toggle flex items-center gap-2 text-neutral-100 hover:text-red-400 transition">
                                <?php if (!empty($displayPic)): ?>
                                    <img src="<?= e($displayPic) ?>" class="w-9 h-9 rounded-full object-cover border border-red-500" alt="Perfil">
                                <?php else: ?>
                                    <span class="w-9 h-9 rounded-full bg-red-600 text-white flex items-center justify-center text-sm font-bold"><?= e($initial) ?></span>
                                <?php endif; ?>
                                <span class="hidden lg:block normal-case text-sm"><?= e($displayName) ?></span>
                            </button>

                            <div class="account-menu-panel hidden absolute right-0 top-12 w-72 bg-neutral-900/95 border border-neutral-700 rounded-xl shadow-2xl overflow-hidden normal-case">
                                <div class="px-4 py-3 border-b border-neutral-700">
                                    <p class="text-sm font-semibold text-white"><?= e($displayName) ?></p>
                                    <p class="text-xs text-neutral-400"><?= e($displayEmail) ?></p>
                                </div>
                                <a href="dashboard.php" class="block px-4 py-3 text-sm hover:bg-neutral-800">Dashboard</a>
                                <a href="logout.php" class="block px-4 py-3 text-sm text-red-400 hover:bg-neutral-800">Terminar Sessão</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php" class="hover:text-red-400 transition">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
        <?php
    }
}

