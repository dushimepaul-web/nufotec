// Traduction du site en 50 langues avec Google Translate (dans le navigateur, gratuit).
// Même moteur que le header PHP : cookie googtrans = /fr/{langue}, retour au français = on efface tout.
// Le contenu reste en français dans l'API et la base de données.

// Les 50 langues les plus parlées, comme sur le site PHP : [code Google, drapeau, nom affiché, libellé de la liste]
export const MAIN_LANGUAGES = [
  ['fr', 'fr', 'Français', 'Français'],
  ['en', 'gb', 'English', 'English — Anglais'],
  ['sw', 'tz', 'Kiswahili', 'Kiswahili — Swahili'],
  ['rn', 'bi', 'Kirundi', 'Kirundi — Rundi'],
  ['zh-CN', 'cn', '中文 (Chinois)', '中文 — Chinois'],
  ['es', 'es', 'Español', 'Español — Espagnol'],
  ['hi', 'in', 'हिन्दी', 'हिन्दी — Hindi'],
  ['ar', 'sa', 'العربية', 'العربية — Arabe'],
  ['bn', 'bd', 'বাংলা', 'বাংলা — Bengali'],
  ['pt', 'pt', 'Português', 'Português — Portugais'],
  ['ru', 'ru', 'Русский', 'Русский — Russe'],
  ['ur', 'pk', 'اردو', 'اردو — Ourdou'],
]

export const OTHER_LANGUAGES = [
  ['id', 'id', 'Bahasa Indonesia', 'Bahasa Indonesia'],
  ['de', 'de', 'Deutsch', 'Deutsch — Allemand'],
  ['ja', 'jp', '日本語', '日本語 — Japonais'],
  ['ms', 'my', 'Bahasa Melayu', 'Bahasa Melayu — Malais'],
  ['tr', 'tr', 'Türkçe', 'Türkçe — Turc'],
  ['ko', 'kr', '한국어', '한국어 — Coréen'],
  ['vi', 'vn', 'Tiếng Việt', 'Tiếng Việt — Vietnamien'],
  ['it', 'it', 'Italiano', 'Italiano — Italien'],
  ['fa', 'ir', 'فارسی', 'فارسی — Persan'],
  ['ta', 'lk', 'தமிழ்', 'தமிழ் — Tamoul'],
  ['th', 'th', 'ภาษาไทย', 'ภาษาไทย — Thaï'],
  ['pl', 'pl', 'Polski', 'Polski — Polonais'],
  ['nl', 'nl', 'Nederlands', 'Nederlands — Néerlandais'],
  ['uk', 'ua', 'Українська', 'Українська — Ukrainien'],
  ['ro', 'ro', 'Română', 'Română — Roumain'],
  ['el', 'gr', 'Ελληνικά', 'Ελληνικά — Grec'],
  ['cs', 'cz', 'Čeština', 'Čeština — Tchèque'],
  ['hu', 'hu', 'Magyar', 'Magyar — Hongrois'],
  ['sv', 'se', 'Svenska', 'Svenska — Suédois'],
  ['he', 'il', 'עברית', 'עברית — Hébreu'],
  ['da', 'dk', 'Dansk', 'Dansk — Danois'],
  ['no', 'no', 'Norsk', 'Norsk — Norvégien'],
  ['fi', 'fi', 'Suomi', 'Suomi — Finnois'],
  ['am', 'et', 'አማርኛ', 'አማርኛ — Amharique'],
  ['so', 'so', 'Soomaali', 'Soomaali — Somali'],
  ['yo', 'ng', 'Yorùbá', 'Yorùbá — Yoruba'],
  ['ha', 'ng', 'Hausa', 'Hausa'],
  ['ig', 'ng', 'Igbo', 'Igbo'],
  ['zu', 'za', 'isiZulu', 'isiZulu — Zoulou'],
  ['af', 'za', 'Afrikaans', 'Afrikaans'],
  ['km', 'kh', 'ខ្មែរ', 'ខ្មែរ — Khmer'],
  ['ne', 'np', 'नेपाली', 'नेपाली — Népalais'],
  ['mr', 'in', 'मराठी', 'मराठी — Marathi'],
  ['te', 'in', 'తెలుగు', 'తెలుగు — Télougou'],
  ['kn', 'in', 'ಕನ್ನಡ', 'ಕನ್ನಡ — Kannada'],
  ['gu', 'in', 'ગુજરાતી', 'ગુજરાતી — Gujarati'],
  ['pa', 'pk', 'ਪੰਜਾਬੀ', 'ਪੰਜਾਬੀ — Pendjabi'],
]

export const ALL_LANGUAGES = [...MAIN_LANGUAGES, ...OTHER_LANGUAGES]
// Drapeaux téléchargés dans public/flags : plus aucun appel à flagcdn.com
export const flagUrl = (flag) => `/flags/${flag}.png`

const STORAGE_KEY = 'nuf_lang'

function setCookie(name, value, days) {
  let exp = ''
  if (days) {
    const d = new Date()
    d.setTime(d.getTime() + days * 864e5)
    exp = `; expires=${d.toUTCString()}`
  }
  document.cookie = `${name}=${value || ''}${exp}; path=/; SameSite=Lax`
  const host = window.location.hostname
  if (host !== 'localhost' && host !== '127.0.0.1') {
    document.cookie = `${name}=${value || ''}${exp}; path=/; domain=.${host}; SameSite=Lax`
  }
}

function getCookie(name) {
  const v = document.cookie.match(`(^|;)\\s*${name}\\s*=\\s*([^;]+)`)
  return v ? v.pop() : ''
}

/** Langue active (cookie Google ou mémoire locale), français par défaut. */
export function getActiveLang() {
  const cookie = getCookie('googtrans')
  if (cookie && cookie !== '/fr/fr') {
    const parts = cookie.split('/')
    return parts[parts.length - 1] || 'fr'
  }
  return localStorage.getItem(STORAGE_KEY) || 'fr'
}

/** Change la langue puis recharge la page pour que Google Translate l'applique. */
export function setLanguage(lang) {
  setCookie('googtrans', '', -1)
  if (lang === 'fr') {
    localStorage.removeItem(STORAGE_KEY)
  } else {
    setCookie('googtrans', `/fr/${lang}`, 365)
    localStorage.setItem(STORAGE_KEY, lang)
  }
  window.location.reload()
}

let started = false

/**
 * Charge Google Translate une seule fois (élément caché) et masque sa barre.
 * Le site est en français : le script de Google (service en ligne, impossible à héberger en local)
 * n'est chargé QUE si le visiteur a choisi une autre langue. En français, aucun appel à Google.
 */
export function initGoogleTranslate() {
  if (started || getActiveLang() === 'fr') return
  started = true
  let holder = document.getElementById('google_translate_element')
  if (!holder) {
    holder = document.createElement('div')
    holder.id = 'google_translate_element'
    holder.style.display = 'none'
    document.body.appendChild(holder)
  }
  window.googleTranslateElementInit = () => {
    new window.google.translate.TranslateElement(
      {
        pageLanguage: 'fr',
        includedLanguages: ALL_LANGUAGES.map(([code]) => code).join(','),
        layout: window.google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false,
      },
      'google_translate_element',
    )
  }
  const script = document.createElement('script')
  script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
  script.async = true
  document.body.appendChild(script)

  const current = getActiveLang()
  if (current !== 'fr') {
    const timer = setInterval(() => {
      const sel = document.querySelector('.goog-te-combo')
      if (sel) {
        clearInterval(timer)
        if (sel.value !== current) {
          sel.value = current
          sel.dispatchEvent(new Event('change'))
        }
      }
    }, 300)
    setTimeout(() => clearInterval(timer), 8000)
  }
  // Supprimer en continu la barre Google Translate
  setInterval(() => {
    const banner = document.querySelector('.goog-te-banner-frame, iframe.skiptranslate')
    if (banner) banner.style.cssText = 'display:none!important;height:0!important;'
    document.body.style.top = '0'
    document.body.style.marginTop = '0'
  }, 800)
}
