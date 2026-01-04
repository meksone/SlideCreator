const CURRENT_SLUG = new URLSearchParams(window.location.search).get('slug') || 'spagna';
document.getElementById('display-slug').innerText = CURRENT_SLUG;
document.title = `Presentazione: ${CURRENT_SLUG.charAt(0).toUpperCase() + CURRENT_SLUG.slice(1)}`;
document.getElementById('main-title').innerText = CURRENT_SLUG;

let dataRepo = {};
let initialContent = {};

const app = {
    async init() {
        try {
            const initialResponse = await fetch('data/default.json');
            initialContent = await initialResponse.json();

            const apiResponse = await fetch(`api/?slug=${CURRENT_SLUG}`);
            const apiData = await apiResponse.json();

            dataRepo = (apiData && apiData.territorio) ? apiData : JSON.parse(JSON.stringify(initialContent));
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

        // 5. Crediti
        const creditiGrid = document.getElementById('crediti-grid');
        creditiGrid.innerHTML = dataRepo.crediti.map((c, idx) => `
            <div class="bg-white rounded-[2rem] shadow-lg overflow-hidden flex flex-col items-center text-center p-8 group hover:-translate-y-2 transition-all duration-300">
                <div class="w-32 h-32 rounded-full overflow-hidden mb-4 border-4 border-stone-100 shadow-inner">
                    <img src="${c.url || 'https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg'}" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-stone-900">${c.name}</h3>
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

        // --- 5. CREDITI ---
        let creditiConfig = dataRepo.crediti.map((c, idx) => `
        <div class="p-6 border border-stone-200 rounded-[2rem] bg-stone-50/50 mb-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <input type="text" value="${c.name}" oninput="dataRepo.crediti[${idx}].name = this.value" class="font-black uppercase border-b bg-transparent outline-none border-stone-300 focus:border-red-600 w-full mr-4">
                <button onclick="app.removeItem('crediti', ${idx})" class="btn-minus">X</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div><label class="text-[9px] font-bold text-stone-400 block uppercase mb-1">URL Immagine</label><input type="text" value="${c.url}" oninput="dataRepo.crediti[${idx}].url = this.value" class="w-full border p-2 text-xs rounded-lg" placeholder="https://..."></div>
            </div>
        </div>`).join('');

        editor.innerHTML += `
        <div class="config-section">
            <div class="flex justify-between items-center mb-8 border-b pb-4"><h3 class="text-xl font-black text-red-600 uppercase">5. CREDITI</h3><button onclick="app.addItem('crediti', {name:'Nuovo Credito', url:''})" class="btn-plus">Aggiungi Credito</button></div>
            ${creditiConfig}
        </div>`;
    },

    async saveToServer() {
        const btn = document.getElementById('btn-save');
        btn.innerText = "⏳ SALVATAGGIO IN CORSO..."; btn.disabled = true;
        try {
            const response = await fetch(`api/?slug=${CURRENT_SLUG}`, {
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
