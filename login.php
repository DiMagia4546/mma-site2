<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MMA 360</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonte -->
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Teko', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-neutral-900 via-slate-800 to-neutral-900 text-neutral-100 flex items-center justify-center relative">

<!-- FUNDO -->
<div class="absolute inset-0">
    <img src="https://cdn.vox-cdn.com/uploads/chorus_image/image/72857030/1254763496.0.jpg"
         class="w-full h-full object-cover opacity-15">
    <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 via-neutral-900/80 to-neutral-900/40"></div>
</div>

<!-- CONTAINER -->
<div class="relative z-10 w-full max-w-5xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-2 bg-neutral-800/80 backdrop-blur rounded-2xl shadow-2xl overflow-hidden">

        <!-- LADO VISUAL -->
        <div class="hidden md:flex flex-col justify-center p-12 bg-gradient-to-b from-slate-700 to-slate-800">
            <h1 class="text-5xl font-bold tracking-widest mb-6 text-neutral-100">
                MMA 360
            </h1>
            <p class="text-2xl text-neutral-200 mb-8">
                A tua plataforma para eventos, atletas e conteúdo exclusivo de MMA.
            </p>

            <ul class="text-xl space-y-4 text-neutral-200">
                <li>✔ Transmissões ao vivo</li>
                <li>✔ Arquivo completo de eventos</li>
                <li>✔ Conteúdo exclusivo</li>
                <li>✔ Qualidade profissional</li>
            </ul>
        </div>

        <!-- FORM -->
        <div class="p-10 md:p-12 bg-neutral-900">
            <div class="mb-10 text-center">
                <img src="pf-removebg-preview.png" class="h-16 mx-auto mb-4">
                <h2 class="text-4xl font-bold tracking-widest text-slate-300">
                    LOGIN
                </h2>
                <p class="text-neutral-400 text-lg">
                    Acede à tua conta
                </p>
            </div>

            <form class="space-y-6">

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Nome</label>
                    <input type="text"
                           class="w-full px-4 py-3 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                </div>

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Email</label>
                    <input type="email"
                           class="w-full px-4 py-3 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                </div>

                <div>
                    <label class="block text-lg mb-1 text-neutral-300">Password</label>
                    <input type="password"
                           class="w-full px-4 py-3 rounded bg-neutral-800 border border-neutral-700 focus:outline-none focus:border-slate-500">
                </div>

                <button
                    class="w-full bg-slate-600 py-3 text-2xl rounded hover:bg-slate-700 transition tracking-widest">
                    ENTRAR
                </button>

            </form>

            <a href="index.php"
               class="block text-center mt-8 text-slate-400 hover:text-slate-300 transition text-lg">
                ← Voltar ao site
            </a>
        </div>

    </div>
</div>

</body>
</html>
