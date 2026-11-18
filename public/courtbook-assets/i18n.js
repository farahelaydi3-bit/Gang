'use strict';

const arabicUI = document.documentElement.lang === 'ar';
const uiLocale = arabicUI ? 'ar-JO' : 'en-GB';
const uiStrings = arabicUI ? JSON.parse(document.querySelector('#courtbook-config').dataset.translations) : {};
const uiNumber = new Intl.NumberFormat(uiLocale, { maximumFractionDigits: 2 });

function uiText(text, values = {}) {
    const translation = Object.hasOwn(uiStrings, text) ? uiStrings[text] : text;
    return translation.replace(/:([a-z]+)/g, (placeholder, key) => values[key] ?? placeholder);
}

function translateFragment(text) {
    const trimmed = text.trim();
    if (!trimmed) return text;
    let translated = uiText(trimmed);
    if (translated === trimmed) {
        translated = trimmed
            .replace(/^(\d+) slots left$/, (_, count) => uiNumber.format(count) + ' أوقات فاضية')
            .replace(/^(\d+) spots left$/, (_, count) => uiNumber.format(count) + ' أماكن فاضية')
            .replace(/^(\d+) courts found$/, (_, count) => uiNumber.format(count) + ' ملاعب لقيناها')
            .replace(/^Players · (.+)$/, 'اللاعبين · $1')
            .replace(/^Queue position #(.+)$/, 'دورك رقم $1')
            .replace(/^Your position is #(.+)\.$/, 'دورك رقم $1.')
            .replace(/^Review booking · (.+)$/, 'راجع الحجز · $1')
            .replace(/^By (.+) · (.+) players$/, 'تنظيم $1 · $2 لاعبين')
            .replace(/^Hosted by (.+)$/, 'المنظّم: $1')
            .replace(/^← Explore courts$/, 'تصفّح الملاعب ←')
            .replace(/^← Find games$/, 'دَوّر على مباراة ←')
            .replace(/^\/ hr$|^\/ hour$/, '/ ساعة')
            .replace(/^\/ player$/, '/ لاعب')
            .replace(/\b(\d+) km\b/g, (_, distance) => uiNumber.format(distance) + ' كم')
            .replace(/\b(\d+) hr\b/g, (_, hours) => uiNumber.format(hours) + ' س')
            .replace(/\s\/ hr\b|\s\/ hour\b/g, ' / ساعة')
            .replace(/\s\/ player\b/g, ' / لاعب')
            .replace(/^(\d+) min ago$/, (_, minutes) => 'قبل ' + uiNumber.format(minutes) + ' دقايق')
            .replace(/^(\d+) days ago$/, (_, days) => 'قبل ' + uiNumber.format(days) + ' أيام');
    }
    return text.replace(trimmed, () => translated);
}

function localizedMarkup(html) {
    if (!arabicUI) return html;
    const template = document.createElement('template');
    template.innerHTML = html;
    const walker = document.createTreeWalker(template.content, NodeFilter.SHOW_TEXT);
    for (let node = walker.nextNode(); node; node = walker.nextNode()) {
        if (!node.parentElement?.closest('[translate="no"]')) node.nodeValue = translateFragment(node.nodeValue);
    }
    for (const element of template.content.querySelectorAll('[aria-label], [title], [alt], [placeholder]')) {
        for (const name of ['aria-label', 'title', 'alt', 'placeholder']) {
            if (element.hasAttribute(name)) element.setAttribute(name, uiText(element.getAttribute(name)));
        }
    }
    return template.innerHTML;
}

