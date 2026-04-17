# INTERNAL_DOCUMENTATION.md - SI Flash Products

## 🆔 Identità del Plugin
**Nome:** SI Flash Products  
**Tagline:** Accesso istantaneo a un database di prodotti pronti per il tuo store WooCommerce.  
**Versione:** 1.1.0 (o come definito in `si-flash-products.php`)

---

## 💎 Proposta di Valore

### 📌 Problema Risolto
Popolare un nuovo store WooCommerce o espandere il catalogo esistente può richiedere settimane di lavoro manuale per l'inserimento di dati, immagini e configurazioni. SI Flash Products risolve questo collo di bottiglia offrendo un database di prodotti pre-configurati importabili con pochi click.

### 🚀 Benefici Chiave
- **Time-to-Market Immediato:** Passa da uno store vuoto a un catalogo completo in pochi minuti.
- **Qualità Garantita:** Prodotti pre-configurati con dati tecnici accurati e immagini ottimizzate.
- **Efficienza Operativa:** Riduce drasticamente il lavoro manuale del personale addetto al caricamento prodotti.

### 🌟 Unique Selling Points (USPs)
- **Instant Access Database:** Un catalogo vasto e costantemente aggiornato a disposizione dei reseller.
- **Configurazione Automatica:** Non solo importa i dati, ma imposta correttamente categorie, tag e attributi WooCommerce.
- **Interfaccia Agile:** Dashboard intuitiva progettata per operazioni di importazione massiva.

---

## 🎯 Marketing & Sales Copy Hooks

### **Target Audience**
- Proprietari di eCommerce in fase di lancio.
- Reseller che operano in settori con cataloghi dinamici.
- Agenzie Web che devono popolare velocemente i siti dei clienti.

### **Elevator Pitch**
"Non perdere tempo a copiare e incollare prodotti. Con SI Flash Products, hai una libreria infinita di articoli pronti per essere venduti sul tuo sito. Scegli, clicca e importa: il tuo business non è mai stato così veloce."

---

## 📖 Guida all'Utilizzo

### **Installazione e Configurazione**
1. Attiva il plugin.
2. Inserisci la tua API Key nella sezione impostazioni (gestita da `SIFlashProducts\Core\License`).
3. Accedi alla dashboard **Flash Products** per sfogliare il catalogo.

### **Funzionalità Core**
- **Catalog Browsing:** Sfoglia e filtra i prodotti disponibili nel database centralizzato.
- **One-Click Import:** Importa il prodotto selezionato direttamente nella tua lista prodotti WooCommerce.
- **Sync Status:** Monitora lo stato delle importazioni e gestisci eventuali aggiornamenti dei dati.

---

## 🛠 Riferimento Tecnico

### **Architettura**
Il plugin utilizza un'architettura moderna con namespace (`SIFlashProducts`) e autoloader PSR-4.
- `includes/Core/Plugin.php`: Il singleton centrale che orchestra il plugin.
- `includes/API/`: Gestisce la comunicazione con il database prodotti esterno.
- `pages/`: Contiene i file di visualizzazione della dashboard amministrativa.

### **Specifiche Tecniche**
- **Namespace:** `SIFlashProducts`
- **Frontend Logic:** Utilizza `functions.js` e `style.css` per una dashboard interattiva e reattiva.
- **Inizializzazione:** Il plugin viene inizializzato tramite la funzione `sifp_init_plugin()`.

### **Hooks & Filters**
- `sifp_after_product_import`: Action scatenata dopo che un prodotto è stato importato con successo.
- `sifp_api_endpoint_filter`: Permette di cambiare l'URL del server database se necessario.

---

## 🗺 Roadmap
- [ ] Supporto per importazione varianti complesse.
- [ ] Sincronizzazione automatica dei prezzi (Prezzi dinamici).
- [ ] Modulo di editing veloce pre-importazione.
