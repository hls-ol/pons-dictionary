const wrapper = document.getElementsByClassName('wrapper')[0];
const searchInput = document.getElementById('search');
const infoText = wrapper.getElementsByClassName('info-text')[0];
const deleteButton = document.getElementById('delete-button');
const contentList = document.getElementById('content-list');
const searchButton = document.getElementById('search-item');

let languageNames = null;
try {
    languageNames = new Intl.DisplayNames(['de'], {type: 'language'});
} catch(e) {
    console.error('Intl LangAPI doesn\'t exist: ' + e);

    languageNames = {
        of: function(name) {
            return name.toUpperCase();
        }
    }
}

const baseURL = window.location.href.split('/').slice(0, -1).join('/');
let dictionary = 'deen';

async function fetchAPI(word) {
    const url = `${baseURL}/search.php?dictionary=${dictionary}&query=${word}`;
    const uri = encodeURI(url);

    const data = await fetch(uri);
    console.log(data);
    const json = data.json();
    console.log(json);

    return json;
}

function renderJson(json) {
    if(json.type === 'error') {
        infoText.innerHTML = json.message;
        return;
    }

    wrapper.classList.add('active');

    infoText.innerHTML = '';
    contentList.innerHTML = '';

    for(const lang of json.data) {
        const langElement = renderLang(lang);

        contentList.append(langElement);
    }
}

function renderLang(langJson) {
    const listElement = document.createElement('li');
    listElement.classList.add('lang');
    const name = document.createElement('h2');
    const langName = langJson.lang;
    name.textContent = `${languageNames.of(langName)}:`;
    listElement.appendChild(name);

    const content = renderContent(langJson.hits);
    listElement.appendChild(name);
    listElement.appendChild(content);

    return listElement;
}

function renderContent(contentJson) {
    const content = document.createElement('div');
    content.classList.add('content');

    const list = document.createElement('ul');
    for(const element of contentJson) {
        switch(element.type) {
            case 'entry':
                for(const rom of element.roms) {
                    const entry = renderEntryROM(rom);
            
                    list.appendChild(entry);
                }
                continue;
            case 'translation':
                const translationElement = document.createElement('li');
                const entry = renderTranslation(element);
                translationElement.appendChild(entry);
                list.appendChild(translationElement);
                continue;
            default:
                console.error(`ElementType ${element.type} is currently not supported. ${JSON.stringify(element)}`);
                continue;
        }
    }

    content.appendChild(list);

    return content;
}

function createTranslationTable() {
    const table = document.createElement('table');
    table.classList.add('translation-table');

    return table;
}

function renderTranslation(element) {
    const table = createTranslationTable();

    const translationElement = renderArabTranslation(element);

    table.appendChild(translationElement);

    return table;
}

function renderEntryROM(entryJson) {
    const entry = document.createElement('li');
    const name = document.createElement('h3');

    if(!entryJson.headword_full) {
        name.textContent = entryJson.headword;
    } else {
        name.innerHTML = entryJson.headword_full;
    }

    entry.appendChild(name);

    const list = document.createElement('ul');

    for(const arabJson of entryJson.arabs) {
        const arabElement = renderArab(arabJson);

        list.appendChild(arabElement);
    }

    entry.appendChild(list);

    return entry;
}

function renderArab(arabJson) {
    const arabElement = document.createElement('li');

    if(arabJson.header !== null && arabJson.header !== '') {
        const span = document.createElement('span');
        span.classList.add('arab-title');
        span.innerHTML = arabJson.header;
        arabElement.appendChild(span);
    }

    const translations = renderArabTranslations(arabJson.translations);
    arabElement.appendChild(translations);

    return arabElement;
}

function renderArabTranslations(translationsJson) {
    const table = createTranslationTable();

    for(const translation of translationsJson) {
        const translationElement = renderArabTranslation(translation);

        table.appendChild(translationElement);
    }

    return table;
}

function renderArabTranslation(translationJson) {
    const row = document.createElement('tr');

    const source = document.createElement('td');
    const target = document.createElement('td');

    if(translationJson.source) {
        source.innerHTML = translationJson.source;
        row.appendChild(source);
    }

    if(translationJson.target) {
        target.innerHTML = translationJson.target;
        row.appendChild(target);
    }

    return row;
}

function formSubmit() {
    if(searchInput.value === null || searchInput.value === '') {
        infoText.innerHTML = 'The query must contain a word';
        
        return;
    }

    search(searchInput.value);
}

document.getElementById('submit-form').addEventListener('submit', event => {
    event.preventDefault();
});

searchButton.addEventListener('click', formSubmit);

deleteButton.addEventListener('click', () => {
    searchInput.value = '';
});

async function search(name) {
    infoText.innerHTML = `Searching for "<span>${name}</span>".`;
    contentList.innerHTML = '';

    const json = await fetchAPI(escapeHtml(name));
    renderJson(json);
}

function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
