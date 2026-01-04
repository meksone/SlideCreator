<?php
/**
 * BACKEND LOGIC - Architettura LAMP (PHP 8.3 + MariaDB)
 * Versione 2.3.1 Stabile - Aggiunta Configurazione Cultura
 */

// Inclusione della configurazione esterna
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    // Fallback di emergenza se il file non esiste
    $db_config = [
        'host' => 'localhost',
        'name' => 'slide_db',
        'user' => 'root', 
        'pass' => '',
    ];
}

$slug = $_GET['slug'] ?? 'spagna';

// Endpoint API integrato per comunicare con MariaDB
if (isset($_GET['api']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $pdo->prepare("SELECT content FROM presentations WHERE slug = ?");
            $stmt->execute([$slug]);
            $row = $stmt->fetch();
            echo $row ? $row['content'] : json_encode(['status' => 'new']);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = file_get_contents('php://input');
            $stmt = $pdo->prepare("INSERT INTO presentations (slug, content) VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE content = VALUES(content)");
            $stmt->execute([$slug, $input]);
            echo json_encode(['status' => 'success']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Errore database: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentazione: <?php echo ucfirst(htmlspecialchars($slug)); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --spanish-red: #dc2626; --spanish-gold: #fbbf24; }
        body { font-family: 'Inter', sans-serif; background-color: #f8f7f5; color: #1c1917; scroll-behavior: smooth; }
        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        .chart-container { position: relative; width: 100%; height: 350px; }
        .active-tab { border-bottom: 3px solid var(--spanish-red); color: var(--spanish-red); font-weight: 700; }
        .fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .img-placeholder { background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-weight: 800; font-size: 0.7rem; border: 2px dashed #d1d5db; overflow: hidden; position: relative; }
        .img-overlay { background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%); }
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .config-section { background: white; border-radius: 2rem; padding: 2.5rem; border: 1px solid #e7e5e4; margin-bottom: 3rem; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.04); }
        .btn-plus { background-color: #dcfce7; color: #16a34a; font-weight: bold; padding: 0.5rem 1rem; border-radius: 0.75rem; transition: all 0.2s; border: none; cursor: pointer; }
        .btn-plus:hover { background-color: #16a34a; color: white; }
        .btn-minus { background-color: #fee2e2; color: #dc2626; font-weight: bold; padding: 0.5rem 1rem; border-radius: 0.75rem; transition: all 0.2s; border: none; cursor: pointer; }
        .btn-minus:hover { background-color: #dc2626; color: white; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <header class="bg-white border-b border-stone-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-red-600">Slide Piattaforma v2.3.1</span>
                    <h1 class="text-2xl font-black text-stone-900 tracking-tighter uppercase" id="main-title"><?php echo htmlspecialchars($slug); ?></h1>
                </div>
            </div>
            <nav class="flex gap-4 md:gap-8 text-xs uppercase tracking-tighter font-bold overflow-x-auto w-full md:w-auto mt-4 md:mt-0">
                <button onclick="app.switchTab('territorio')" id="tab-territorio" class="active-tab py-2 whitespace-nowrap">Geografia</button>
                <button onclick="app.switchTab('societa')" id="tab-societa" class="inactive-tab py-2 whitespace-nowrap">Società & Città</button>
                <button onclick="app.switchTab('cultura')" id="tab-cultura" class="inactive-tab py-2 whitespace-nowrap">Cultura & Arte</button>
                <button onclick="app.switchTab('economia')" id="tab-economia" class="inactive-tab py-2 whitespace-nowrap">Economia</button>
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

    <script>
        const CURRENT_SLUG = "<?php echo addslashes($slug); ?>";
        document.getElementById('display-slug').innerText = CURRENT_SLUG;

        // Dati Iniziali Completi (Contenuti v1.3)
        const initialContent = {
            territorio: {
                titolo: "Geografia Fisica e Territorio",
                descrizione: "La Spagna occupa circa l'85% della Penisola Iberica ed è il secondo paese più montuoso d'Europa dopo la Svizzera. Confina a nord con la Francia e l'Andorra (Pirenei), a ovest con il Portogallo e a sud con Gibilterra e il Marocco.",
                mappa_url: "", mappa_placeholder: "",
                mari_coste: "La Spagna è bagnata dal Mar Mediterraneo a est e a sud, dall'Oceano Atlantico a ovest e dal Mar Cantabrico a nord. Le coste sono variegate: alte e frastagliate in Galizia, sabbiose nel Mediterraneo.",
                isole: "Comprende l'arcipelago delle Baleari nel Mediterraneo e l'arcipelago delle Canarie nell'Oceano Atlantico di origine vulcanica.",
                monti_pianure: ["Pirenei", "Sistemi Betici (Sierra Nevada)", "Sistema Centrale", "Cordigliera Cantabrica", "Depressione dell'Ebro", "Depressione Betica"],
                fiumi_laghi: ["Tago (1.007 km)", "Ebro (830 km)", "Duero", "Guadalquivir", "Lago di Sanabria"]
            },
            cities: [
                { title: "Madrid", url: "", placeholder: "", pop: "3.3 Milioni", foundation: "Fondata nel IX sec.", history: "Originariamente Mayrit araba", wiki: "Madrid è la capitale della Spagna. Situata al centro della penisola sul fiume Manzanarre, è il fulcro politico e culturale del paese, sede del governo e del Re.", landmarks: [{icon:"🖼️", name:"Museo del Prado", desc:"Una delle pinacoteche più importanti al mondo."}] },
                { title: "Barcellona", url: "", placeholder: "", pop: "1.6 Milioni", foundation: "Fondata dai Romani", history: "Antica Barcino", wiki: "Capitale della Catalogna e seconda città del paese. Famosa per il design modernista unico di Antoni Gaudí e il suo vivace porto mediterraneo.", landmarks: [{icon:"🦎", name:"Sagrada Família", desc:"Basilica incompiuta simbolo globale di Gaudí."}] },
                { title: "Granada", url: "", placeholder: "", pop: "232 Mila", foundation: "VIII sec.", history: "Ultimo regno Nazari", wiki: "Ai piedi della Sierra Nevada, Granada è il simbolo della fusione tra cultura araba e cristiana. Celebre per l'Alhambra, ultimo baluardo arabo in Spagna.", landmarks: [{icon:"🏰", name:"Alhambra", desc:"Palazzo reale e cittadella dei sultani arabi."}] },
                { title: "Cordoba", url: "", placeholder: "", pop: "325 Mila", foundation: "Romana", history: "Capitale del Califfato", wiki: "Famosa per essere stata la città più colta d'Europa nel X secolo. Vanta una densità altissima di siti UNESCO tra vicoli bianchi e cortili fioriti.", landmarks: [{icon:"🕌", name:"Mezquita", desc:"Moschea-Cattedrale straordinaria con arcate rosse e bianche."}] }
            ],
            cultura: [
                { label: "San Fermín", desc: "La celebre corsa dei tori (Encierro) a Pamplona. Migliaia di persone corrono davanti a sei tori selvaggi per le strade medievali.", url: "", placeholder: "" },
                { label: "Il Flamenco", desc: "Patrimonio UNESCO. Arte che unisce canto, chitarra e ballo, nata dall'unione di culture gitane, arabe ed ebree in Andalusia.", url: "", placeholder: "[Image of Flamenco]" },
                { label: "Las Fallas", desc: "Valencia accoglie la primavera bruciando monumentali sculture di cartapesta (ninots) in un rito di fuoco e satira.", url: "", placeholder: "" },
                { label: "Semana Santa", desc: "Solenni processioni pasquali con i monumentali 'Pasos' religiosi portati a spalla, tipiche di Siviglia.", url: "", placeholder: "" }
            ],
            economia: {
                chartData: [
                    { label: 'Servizi', value: 75 },
                    { label: 'Industria', value: 20 },
                    { label: 'Agricoltura', value: 5 }
                ],
                items: [
                    { label: "Servizi e Turismo", desc: "Pilastro della nazione (75% PIL). Con 80+ milioni di turisti, la Spagna è una superpotenza dell'ospitalità.", url: "", placeholder: "" },
                    { label: "Industria Auto", desc: "2° produttore europeo di autoveicoli. Sede di importanti stabilimenti e leader nelle energie rinnovabili.", url: "", placeholder: "" },
                    { label: "Innovazione", desc: "Hub tecnologici in crescita a Barcellona e Madrid, con focus su BioTech e Smart Cities.", url: "", placeholder: "" }
                ],
                resources: "Il sottosuolo spagnolo è tra i più ricchi d'Europa per minerali. Primo produttore mondiale di mercurio e gesso, con importanti giacimenti di piombo e uranio."
            }
        };

        let dataRepo = {};

        const app = {
            async init() {
                try {
                    const response = await fetch(`?api=true&slug=${CURRENT_SLUG}`);
                    const data = await response.json();
                    dataRepo = (data && data.territorio) ? data : JSON.parse(JSON.stringify(initialContent));
                } catch (e) {
                    dataRepo = JSON.parse(JSON.stringify(initialContent));
                }
                this.renderAll();
                this.setupCharts();
            },

            renderAll() {
                // 1. Geografia
                document.getElementById('geo-title').innerText = dataRepo.territorio.titolo;
                document.getElementById('geo-desc').innerText = dataRepo.territorio.descrizione;
                document.getElementById('geo-info-container').innerHTML = `
                    <div><h4 class="text-[10px] font-black text-red-600 uppercase tracking-widest mb-2">Mari e Coste</h4><p class="text-sm text-stone-600 leading-relaxed">${dataRepo.territorio.mari_coste}</p></div>
                    <div><h4 class="text-[10px] font-black text-red-600 uppercase tracking-widest mb-2">Isole</h4><p class="text-sm text-stone-600 leading-relaxed">${dataRepo.territorio.isole}</p></div>
                `;
                document.getElementById('geo-list-container').innerHTML = `
                    <div><h4 class="text-lg font-bold text-stone-900 border-b border-stone-100 pb-3 mb-6">Monti e pianure</h4><ul class="space-y-3 text-sm text-stone-500">
                        ${dataRepo.territorio.monti_pianure.map(m => `<li class="flex items-center gap-3"><span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>${m}</li>`).join('')}
                    </ul></div>
                    <div><h4 class="text-lg font-bold text-stone-900 border-b border-stone-100 pb-3 mb-6">Fiumi e laghi principali</h4><ul class="space-y-3 text-sm text-stone-500">
                        ${dataRepo.territorio.fiumi_laghi.map(f => `<li class="flex items-center gap-3"><span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>${f}</li>`).join('')}
                    </ul></div>
                `;
                this.renderMedia('geo-hero-img', dataRepo.territorio.mappa_url, dataRepo.territorio.mappa_placeholder);

                // 2. Città
                const selector = document.getElementById('city-selector');
                selector.innerHTML = dataRepo.cities.map((c, idx) => `
                    <button onclick="app.selectCity(${idx})" id="btn-city-${idx}" class="city-btn w-full text-left px-5 py-3 rounded-xl bg-white text-stone-600 border border-stone-100 font-bold text-sm transition-all">${c.title}</button>
                `).join('');
                if (dataRepo.cities.length > 0) this.selectCity(0);

                // 3. Cultura
                document.getElementById('cultura-grid').innerHTML = dataRepo.cultura.map((c, idx) => `
                    <div class="bg-white rounded-[2rem] shadow-lg overflow-hidden flex flex-col group hover:-translate-y-2 transition-all duration-300">
                        <div class="h-56 img-placeholder" id="placeholder-cultura-${idx}">${c.placeholder}</div>
                        <div class="p-8 flex-grow"><h3 class="text-xl font-bold mb-4">${c.label}</h3><p class="text-stone-600 text-sm leading-relaxed">${c.desc}</p></div>
                    </div>
                `).join('');
                dataRepo.cultura.forEach((c, idx) => this.renderMedia(`placeholder-cultura-${idx}`, c.url, c.placeholder));

                // 4. Economia
                document.getElementById('economia-grid').innerHTML = dataRepo.economia.items.map((i, idx) => `
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-stone-100">
                        <h4 class="text-[10px] font-black uppercase text-red-600 mb-2 tracking-widest">${i.label}</h4>
                        <p class="text-sm text-stone-600 mb-6 h-12 leading-relaxed">${i.desc}</p>
                        <div class="h-40 img-placeholder" id="placeholder-economia-${idx}">${i.placeholder}</div>
                    </div>
                `).join('');
                document.getElementById('economy-resources').innerText = dataRepo.economia.resources;
                dataRepo.economia.items.forEach((i, idx) => this.renderMedia(`placeholder-economia-${idx}`, i.url, i.placeholder));
                
                document.getElementById('economy-legend').innerHTML = dataRepo.economia.chartData.map((item) => `
                    <div class="flex justify-between items-center text-xs border-b border-stone-50 pb-3">
                        <span class="text-stone-500 font-medium">${item.label}</span>
                        <span class="font-black text-stone-900">${item.value}%</span>
                    </div>
                `).join('');
            },

            selectCity(idx) {
                const d = dataRepo.cities[idx];
                if (!d) return;
                const p = document.getElementById('city-content-panel');
                p.classList.remove('fade-in'); void p.offsetWidth; p.classList.add('fade-in');
                document.getElementById('city-title').innerText = d.title;
                document.getElementById('city-pop-label').innerText = d.pop;
                document.getElementById('city-foundation').innerText = d.foundation;
                document.getElementById('city-history').innerText = d.history;
                document.getElementById('city-wikipedia').innerText = d.wiki;
                document.getElementById('city-landmarks-list').innerHTML = d.landmarks.map(l => `
                    <div class="flex gap-4 items-start">
                        <span class="text-2xl">${l.icon || '📍'}</span>
                        <div><strong class="text-stone-900 text-sm block">${l.name}</strong><p class="text-[11px] text-stone-500 leading-snug">${l.desc}</p></div>
                    </div>
                `).join('');
                this.renderMedia('city-img-display', d.url, d.placeholder);
                document.querySelectorAll('.city-btn').forEach(b => b.className = "city-btn w-full text-left px-5 py-3 rounded-xl bg-white text-stone-600 border border-stone-100 font-bold text-sm");
                const targetBtn = document.getElementById(`btn-city-${idx}`);
                if (targetBtn) targetBtn.className = "city-btn w-full text-left px-5 py-3 rounded-xl bg-red-600 text-white font-black shadow-lg border-transparent";
            },

            renderMedia(cid, url, placeholder) {
                const c = document.getElementById(cid);
                if (!c) return;
                if (url && url.trim() !== "") {
                    c.innerHTML = `<img src="${url}" class="w-full h-full object-cover fade-in">`;
                    c.classList.remove('img-placeholder');
                } else {
                    c.innerHTML = `<span class="opacity-40 tracking-tighter uppercase font-black px-4 text-center text-xs">${placeholder}</span>`;
                    c.classList.add('img-placeholder');
                }
            },

            switchTab(id) {
                document.querySelectorAll('section').forEach(s => s.classList.add('hidden'));
                document.getElementById(`view-${id}`).classList.remove('hidden');
                document.querySelectorAll('header nav button').forEach(b => b.classList.remove('active-tab'));
                const tab = document.getElementById(`tab-${id}`);
                if (tab) tab.classList.add('active-tab');
                if (id === 'config') this.renderConfigEditor();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            addItem(path, template) {
                let current = dataRepo;
                const steps = path.split('.');
                steps.forEach((step, idx) => {
                    if (idx === steps.length - 1) current[step].push(JSON.parse(JSON.stringify(template)));
                    else current = current[step];
                });
                this.renderConfigEditor();
            },

            removeItem(path, index) {
                let current = dataRepo;
                const steps = path.split('.');
                steps.forEach((step, idx) => {
                    if (idx === steps.length - 1) current[step].splice(index, 1);
                    else current = current[step];
                });
                this.renderConfigEditor();
            },

            renderConfigEditor() {
                const editor = document.getElementById('config-editor');
                editor.innerHTML = '';

                // --- 1. GEOGRAFIA ---
                editor.innerHTML += `
                <div class="config-section">
                    <h3 class="text-xl font-black mb-8 text-red-600 border-b pb-4">1. GEOGRAFIA</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div><label class="text-[10px] font-bold text-stone-400 uppercase">Titolo</label><input type="text" value="${dataRepo.territorio.titolo}" oninput="dataRepo.territorio.titolo = this.value" class="w-full border p-2 rounded-lg bg-stone-50 mt-1"></div>
                        <div><label class="text-[10px] font-bold text-stone-400 uppercase">URL Mappa Hero</label><input type="text" value="${dataRepo.territorio.mappa_url}" oninput="dataRepo.territorio.mappa_url = this.value" class="w-full border p-2 rounded-lg bg-stone-50 mt-1"></div>
                        <div class="md:col-span-2"><label class="text-[10px] font-bold text-stone-400 uppercase">Descrizione Generale</label><textarea oninput="dataRepo.territorio.descrizione = this.value" class="w-full border p-2 rounded-lg bg-stone-50 h-24 mt-1">${dataRepo.territorio.descrizione}</textarea></div>
                        <div><label class="text-[10px] font-bold text-stone-400 uppercase">Mari e Coste</label><textarea oninput="dataRepo.territorio.mari_coste = this.value" class="w-full border p-2 rounded-lg bg-stone-50 h-24 mt-1">${dataRepo.territorio.mari_coste}</textarea></div>
                        <div><label class="text-[10px] font-bold text-stone-400 uppercase">Isole</label><textarea oninput="dataRepo.territorio.isole = this.value" class="w-full border p-2 rounded-lg bg-stone-50 h-24 mt-1">${dataRepo.territorio.isole}</textarea></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <div class="flex justify-between items-center mb-4"><label class="text-[10px] uppercase text-stone-400">Monti e Pianure</label><button onclick="app.addItem('territorio.monti_pianure', 'Nuova Voce')" class="btn-plus">+</button></div>
                            ${dataRepo.territorio.monti_pianure.map((m, i) => `
                                <div class="flex gap-2 mb-2"><input type="text" value="${m}" oninput="dataRepo.territorio.monti_pianure[${i}] = this.value" class="flex-grow border p-2 rounded-lg text-xs"><button onclick="app.removeItem('territorio.monti_pianure', ${i})" class="btn-minus">-</button></div>`).join('')}
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-4"><label class="text-[10px] uppercase text-stone-400">Fiumi e Laghi</label><button onclick="app.addItem('territorio.fiumi_laghi', 'Nuova Voce')" class="btn-plus">+</button></div>
                            ${dataRepo.territorio.fiumi_laghi.map((f, i) => `
                                <div class="flex gap-2 mb-2"><input type="text" value="${f}" oninput="dataRepo.territorio.fiumi_laghi[${i}] = this.value" class="flex-grow border p-2 rounded-lg text-xs"><button onclick="app.removeItem('territorio.fiumi_laghi', ${i})" class="btn-minus">-</button></div>`).join('')}
                        </div>
                    </div>
                </div>`;

                // --- 2. CITTÀ ---
                let cityConfig = dataRepo.cities.map((c, idx) => `
                <div class="p-6 border border-stone-200 rounded-[2rem] bg-stone-50/50 mb-8 shadow-sm">
                    <div class="flex justify-between items-center mb-4"><input type="text" value="${c.title}" oninput="dataRepo.cities[${idx}].title = this.value" class="font-black uppercase border-b bg-transparent outline-none border-stone-300 focus:border-red-600 text-lg"><button onclick="app.removeItem('cities', ${idx})" class="btn-minus">X</button></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <input type="text" value="${c.pop}" oninput="dataRepo.cities[${idx}].pop = this.value" placeholder="Pop" class="border p-2 text-xs rounded-lg">
                        <input type="text" value="${c.foundation}" oninput="dataRepo.cities[${idx}].foundation = this.value" placeholder="Fond" class="border p-2 text-xs rounded-lg">
                        <input type="text" value="${c.url}" oninput="dataRepo.cities[${idx}].url = this.value" placeholder="URL Foto" class="border p-2 text-xs rounded-lg">
                    </div>
                    <textarea oninput="dataRepo.cities[${idx}].wiki = this.value" class="w-full border p-2 text-xs h-24 rounded-lg mb-4">${c.wiki}</textarea>
                    <div class="bg-white p-4 rounded-xl border border-stone-200 shadow-inner">
                        <div class="flex justify-between items-center mb-2"><h5 class="text-[10px] font-black uppercase text-red-600">Attrazioni</h5><button onclick="app.addItem('cities.${idx}.landmarks', {icon:'📍', name:'', desc:''})" class="btn-plus">+</button></div>
                        ${c.landmarks.map((l, lidx) => `
                        <div class="grid grid-cols-4 gap-2 mb-2 items-end"><input type="text" value="${l.icon}" oninput="dataRepo.cities[${idx}].landmarks[${lidx}].icon = this.value" class="border p-1 text-xs rounded">
                        <input type="text" value="${l.name}" oninput="dataRepo.cities[${idx}].landmarks[${lidx}].name = this.value" class="border p-1 text-xs rounded col-span-1">
                        <input type="text" value="${l.desc}" oninput="dataRepo.cities[${idx}].landmarks[${lidx}].desc = this.value" class="border p-1 text-xs rounded col-span-1">
                        <button onclick="app.removeItem('cities.${idx}.landmarks', ${lidx})" class="btn-minus h-full">-</button></div>`).join('')}
                    </div>
                </div>`).join('');
                editor.innerHTML += `<div class="config-section"><div class="flex justify-between items-center mb-8 border-b pb-4"><h3 class="text-xl font-black text-red-600 uppercase">2. SOCIETÀ E CITTÀ</h3><button onclick="app.addItem('cities', {title:'Nuova Città', url:'', pop:'', foundation:'', history:'', wiki:'', landmarks:[]})" class="btn-plus">Aggiungi Città</button></div>${cityConfig}</div>`;

                // --- 3. CULTURA E ARTE ---
                let cultureConfig = dataRepo.cultura.map((c, idx) => `
                <div class="p-6 border border-stone-200 rounded-[2rem] bg-stone-50/50 mb-6 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <input type="text" value="${c.label}" oninput="dataRepo.cultura[${idx}].label = this.value" class="font-black uppercase border-b bg-transparent outline-none border-stone-300 focus:border-red-600 w-full mr-4">
                        <button onclick="app.removeItem('cultura', ${idx})" class="btn-minus">X</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div><label class="text-[9px] font-bold text-stone-400 block uppercase mb-1">URL Immagine</label><input type="text" value="${c.url}" oninput="dataRepo.cultura[${idx}].url = this.value" class="w-full border p-2 text-xs rounded-lg" placeholder="https://..."></div>
                        <div><label class="text-[9px] font-bold text-stone-400 block uppercase mb-1">Placeholder (se vuoto)</label><input type="text" value="${c.placeholder}" oninput="dataRepo.cultura[${idx}].placeholder = this.value" class="w-full border p-2 text-xs rounded-lg"></div>
                    </div>
                    <div><label class="text-[9px] font-bold text-stone-400 block uppercase mb-1">Descrizione</label><textarea oninput="dataRepo.cultura[${idx}].desc = this.value" class="w-full border p-2 text-xs h-24 rounded-lg">${c.desc}</textarea></div>
                </div>`).join('');

                editor.innerHTML += `
                <div class="config-section">
                    <div class="flex justify-between items-center mb-8 border-b pb-4"><h3 class="text-xl font-black text-red-600 uppercase">3. CULTURA E ARTE</h3><button onclick="app.addItem('cultura', {label:'Nuovo Elemento', desc:'', url:'', placeholder:'[Nuova Immagine]'})" class="btn-plus">Aggiungi Evento</button></div>
                    ${cultureConfig}
                </div>`;

                // --- 4. ECONOMIA & PIL ---
                let pilConfig = dataRepo.economia.chartData.map((item, idx) => `
                    <div class="grid grid-cols-3 gap-4 mb-2">
                        <input type="text" value="${item.label}" oninput="dataRepo.economia.chartData[${idx}].label = this.value" class="border p-2 rounded-lg text-xs col-span-1" placeholder="Settore">
                        <input type="number" value="${item.value}" oninput="dataRepo.economia.chartData[${idx}].value = parseInt(this.value)" class="border p-2 rounded-lg text-xs" placeholder="%">
                        <button onclick="app.removeItem('economia.chartData', ${idx})" class="btn-minus">-</button>
                    </div>
                `).join('');

                let ecoItemsConfig = dataRepo.economia.items.map((i, idx) => `
                <div class="p-4 border border-stone-100 rounded-2xl bg-white mb-4 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                        <input type="text" value="${i.label}" oninput="dataRepo.economia.items[${idx}].label = this.value" class="font-bold border-b text-xs outline-none">
                        <input type="text" value="${i.url}" oninput="dataRepo.economia.items[${idx}].url = this.value" placeholder="URL Foto" class="border p-2 text-xs rounded-lg">
                    </div>
                    <textarea oninput="dataRepo.economia.items[${idx}].desc = this.value" class="w-full border p-2 text-xs h-16 rounded-lg">${i.desc}</textarea>
                    <button onclick="app.removeItem('economia.items', ${idx})" class="mt-2 text-[9px] text-red-600 font-bold uppercase tracking-widest">Rimuovi Settore</button>
                </div>`).join('');

                editor.innerHTML += `
                <div class="config-section">
                    <h3 class="text-xl font-black mb-8 text-red-600 border-b pb-4 uppercase text-center md:text-left">4. ECONOMIA & GRAFICO PIL</h3>
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4"><label class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">Ripartizione del PIL (Dati Grafico)</label>
                             <button onclick="app.addItem('economia.chartData', {label:'', value:0})" class="btn-plus">Aggiungi Voce</button></div>
                        <div class="bg-stone-50 p-6 rounded-2xl border border-stone-200">${pilConfig}</div>
                    </div>
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4"><label class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">Blocchi di Settore</label>
                             <button onclick="app.addItem('economia.items', {label:'Nuovo Settore', desc:'', url:'', placeholder:'[Nuova Foto]'})" class="btn-plus">Aggiungi Blocco</button></div>
                        <div class="bg-stone-50 p-6 rounded-2xl border border-stone-200">${ecoItemsConfig}</div>
                    </div>
                    <div><label class="text-[10px] font-bold text-stone-400 uppercase">Testo Risorse Naturali</label>
                         <textarea oninput="dataRepo.economia.resources = this.value" class="w-full border p-2 rounded-lg bg-stone-50 h-24 mt-1">${dataRepo.economia.resources}</textarea></div>
                </div>`;
            },

            async saveToServer() {
                const btn = document.getElementById('btn-save');
                btn.innerText = "⏳ SALVATAGGIO IN CORSO..."; btn.disabled = true;
                try {
                    const response = await fetch(`?api=true&slug=${CURRENT_SLUG}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(dataRepo)
                    });
                    const res = await response.json();
                    if (res.status === 'success') { 
                        btn.innerText = "✅ DATI SALVATI SUL SERVER!"; 
                        this.renderAll(); 
                        this.setupCharts(); 
                    }
                } catch (e) { btn.innerText = "❌ ERRORE DI CONNESSIONE"; }
                setTimeout(() => { btn.innerText = "💾 SALVA SUL SERVER"; btn.disabled = false; }, 2500);
            },

            exportConfig() {
                const blob = new Blob([JSON.stringify(dataRepo, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a'); a.href = url; a.download = `backup-${CURRENT_SLUG}.json`; a.click();
            },

            setupCharts() {
                const canvas = document.getElementById('sectorChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (window.myChart) window.myChart.destroy();
                
                const labels = dataRepo.economia.chartData.map(i => i.label);
                const values = dataRepo.economia.chartData.map(i => i.value);

                window.myChart = new Chart(ctx, {
                    type: 'polarArea',
                    data: { 
                        labels: labels, 
                        datasets: [{ 
                            data: values, 
                            backgroundColor: [
                                'rgba(220, 38, 38, 0.7)', 
                                'rgba(31, 41, 55, 0.7)', 
                                'rgba(16, 185, 129, 0.7)',
                                'rgba(251, 191, 36, 0.7)',
                                'rgba(59, 130, 246, 0.7)',
                                'rgba(139, 92, 246, 0.7)',
                                'rgba(168, 85, 247, 0.7)'
                            ] 
                        }] 
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
        };

        app.init();
    </script>
</body>
</html>