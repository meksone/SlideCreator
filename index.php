<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentazione</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white border-b border-stone-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-red-600">Slide Piattaforma v2.3.1</span>
                    <h1 class="text-2xl font-black text-stone-900 tracking-tighter uppercase" id="main-title"></h1>
                </div>
            </div>
            <nav class="flex gap-4 md:gap-8 text-xs uppercase tracking-tighter font-bold overflow-x-auto w-full md:w-auto mt-4 md:mt-0">
                <button onclick="app.switchTab('territorio')" id="tab-territorio" class="active-tab py-2 whitespace-nowrap">Geografia</button>
                <button onclick="app.switchTab('societa')" id="tab-societa" class="inactive-tab py-2 whitespace-nowrap">Società & Città</button>
                <button onclick="app.switchTab('cultura')" id="tab-cultura" class="inactive-tab py-2 whitespace-nowrap">Cultura & Arte</button>
                <button onclick="app.switchTab('economia')" id="tab-economia" class="inactive-tab py-2 whitespace-nowrap">Economia</button>
                <button onclick="app.switchTab('crediti')" id="tab-crediti" class="inactive-tab py-2 whitespace-nowrap">Crediti</button>
            </nav>
        </div>
    </header>

    <main class="flex-grow max-w-7xl mx-auto px-6 py-10 w-full">
        
        <!-- SEZIONE 1: GEOGRAFIA -->
        <section id="view-territorio" class="fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
                <div class="lg:col-span-1 space-y-8">
                    <div>
                        <h2 class="text-4xl font-black text-stone-900 leading-none mb-4" id="geo-title"></h2>
                        <p class="text-stone-600 text-sm leading-relaxed" id="geo-desc"></p>
                    </div>
                    <div id="geo-info-container" class="space-y-8 bg-white p-8 rounded-[2rem] border border-stone-100 shadow-sm"></div>
                </div>
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-[2.5rem] shadow-2xl border border-stone-100 overflow-hidden">
                        <div class="h-96 relative">
                            <div id="geo-hero-img" class="img-placeholder w-full h-full"></div>
                            <div class="absolute inset-0 img-overlay flex items-end p-10 text-white">
                                <h3 class="text-3xl font-black">Morfologia del Territorio</h3>
                            </div>
                        </div>
                        <div class="p-12 grid grid-cols-1 md:grid-cols-2 gap-12 bg-white" id="geo-list-container"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEZIONE 2: SOCIETÀ E CITTÀ -->
        <section id="view-societa" class="hidden fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-1 space-y-2 overflow-y-auto max-h-[70vh] custom-scroll pr-2" id="city-selector"></div>
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-[2.5rem] shadow-2xl border border-stone-100 flex flex-col md:flex-row min-h-[650px] overflow-hidden">
                        <div class="w-full md:w-1/2 relative">
                            <div id="city-img-display" class="img-placeholder w-full h-full"></div>
                        </div>
                        <div class="w-full md:w-1/2 p-12 flex flex-col justify-between">
                            <div id="city-content-panel" class="fade-in">
                                <div class="flex items-center gap-3 mb-4">
                                    <span id="city-pop-label" class="bg-stone-100 text-stone-600 text-[10px] font-bold uppercase px-3 py-1.5 rounded-full"></span>
                                    <span id="city-foundation" class="text-[10px] text-stone-400 font-bold uppercase tracking-wider"></span>
                                </div>
                                <h3 id="city-title" class="text-5xl font-black text-stone-900 tracking-tight"></h3>
                                <p id="city-history" class="text-sm text-red-600 font-medium italic mt-2"></p>
                                <div id="city-wikipedia" class="text-sm text-stone-600 leading-relaxed my-8 h-48 overflow-y-auto custom-scroll pr-4"></div>
                            </div>
                            <div class="bg-stone-50 p-6 rounded-3xl border border-stone-100 shadow-inner">
                                <h4 class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-4 border-b border-stone-200 pb-2 text-center md:text-left">Principali Attrazioni</h4>
                                <div id="city-landmarks-list" class="space-y-4 max-h-40 overflow-y-auto custom-scroll pr-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEZIONE 3: CULTURA -->
        <section id="view-cultura" class="hidden fade-in">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-black text-stone-900">Patrimonio e Tradizioni</h2>
                <p class="text-stone-500 mt-2">Dettagli ed espressioni popolari uniche della nazione.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="cultura-grid"></div>
        </section>

        <!-- SEZIONE 4: ECONOMIA -->
        <section id="view-economia" class="hidden fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-8 space-y-8">
                    <h2 class="text-4xl font-black text-stone-900 leading-none">Analisi Economica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="economia-grid"></div>
                    <div class="bg-stone-900 p-10 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden">
                        <div class="absolute -right-6 -top-6 text-[12rem] opacity-5 select-none font-serif">ECON</div>
                        <h3 class="text-2xl font-bold text-amber-500 mb-4 flex items-center gap-3">💎 Risorse Naturali</h3>
                        <p class="text-sm text-stone-400 leading-relaxed max-w-2xl" id="economy-resources"></p>
                    </div>
                </div>
                <div class="lg:col-span-4">
                    <div class="bg-white p-10 rounded-[2.5rem] shadow-xl border border-stone-100 h-full flex flex-col">
                        <h3 class="text-center font-bold uppercase text-[10px] text-stone-400 mb-10 tracking-[0.3em]">Composizione PIL</h3>
                        <div class="chart-container flex-grow"><canvas id="sectorChart"></canvas></div>
                        <div id="economy-legend" class="mt-10 space-y-4"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEZIONE CONFIGURAZIONE -->
        <section id="view-config" class="hidden fade-in pb-20">
            <div class="max-w-5xl mx-auto">
                <div class="bg-stone-900 text-white p-10 rounded-[3rem] shadow-2xl mb-12 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="text-center md:text-left">
                        <h2 class="text-4xl font-black tracking-tighter">Pannello Editor</h2>
                        <p class="text-amber-500 mt-2 font-medium opacity-80 uppercase text-[10px] tracking-widest">Sincronizzazione MariaDB v2.3.1 attiva</p>
                    </div>
                    <button onclick="app.saveToServer()" id="btn-save" class="bg-red-600 text-white font-black px-10 py-5 rounded-2xl transition-all shadow-xl hover:scale-105 active:scale-95">
                        💾 SALVA SUL SERVER
                    </button>
                </div>
                <div id="config-editor" class="space-y-8"></div>
            </div>
        </section>

        <!-- SEZIONE 5: CREDITI -->
        <section id="view-crediti" class="hidden fade-in">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-black text-stone-900">Crediti</h2>
                <p class="text-stone-500 mt-2">Un ringraziamento speciale a chi ha contribuito a questo progetto.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8" id="crediti-grid"></div>
        </section>
    </main>

    <footer class="bg-white border-t border-stone-200 py-10 mt-auto">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-stone-400 text-[10px] font-bold uppercase tracking-widest">
                ID PRESENTAZIONE: <span class="text-stone-900" id="display-slug"></span>
            </div>
            <div class="flex gap-4">
                <button onclick="app.exportConfig()" class="text-[10px] font-black uppercase text-stone-400 hover:text-stone-900">Backup JSON</button>
                <button onclick="app.switchTab('config')" class="text-[10px] font-black uppercase bg-stone-100 text-stone-600 hover:bg-red-600 hover:text-white transition-all py-3 px-8 rounded-full border border-stone-200 shadow-sm">
                    ⚙️ Impostazioni Totali
                </button>
            </div>
        </div>
    </footer>

    <script src="js/app.js"></script>
</body>
</html>