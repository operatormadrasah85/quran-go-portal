'use strict';

/* =========================================================================
   0. KONFIGURASI MODE DEMO TERBATAS (AKTIF HANYA JIKA window.DEMO_CONFIG.enabled = true)
   ========================================================================= */
const DEMO_CFG = Object.assign({
  enabled: false,
  allowedPages: [1, 582, 604],
  maxVerses: 5,
  buyUrl: './checkout.php',
  waUrl: ''
}, window.DEMO_CONFIG || {});

function isPageAllowedInDemo(pageNum) {
  if (!DEMO_CFG.enabled) return true;
  return DEMO_CFG.allowedPages.indexOf(Number(pageNum)) !== -1;
}

function isJuzAllowedInDemo(juzObj) {
  if (!DEMO_CFG.enabled) return true;
  for (let p = juzObj.startPage; p <= juzObj.endPage; p++) {
    if (isPageAllowedInDemo(p)) return true;
  }
  return false;
}

function showDemoLockModal(reasonText) {
  const modal = document.getElementById('modal-demo-lock');
  const desc = document.getElementById('demo-lock-desc');
  if (desc && reasonText) desc.textContent = reasonText;
  if (modal) {
    modal.classList.remove('hidden');
  } else {
    showToast(reasonText || 'Fitur ini hanya tersedia di Versi Full 30 Juz!');
  }
}


/* =========================================================================
   1. PETA 30 JUZ, 114 SURAH, & JUMLAH AYAT (HAFS 6236 AYAT)
   ========================================================================= */
const JUZ_LIST = [
  { juz: 1,  surah: "Al-Fatihah",   startPage: 1,   endPage: 21  },
  { juz: 2,  surah: "Al-Baqarah",   startPage: 22,  endPage: 41  },
  { juz: 3,  surah: "Al-Baqarah",   startPage: 42,  endPage: 61  },
  { juz: 4,  surah: "Ali 'Imran",   startPage: 62,  endPage: 81  },
  { juz: 5,  surah: "An-Nisa'",     startPage: 82,  endPage: 101 },
  { juz: 6,  surah: "An-Nisa'",     startPage: 102, endPage: 121 },
  { juz: 7,  surah: "Al-Ma'idah",   startPage: 122, endPage: 141 },
  { juz: 8,  surah: "Al-An'am",     startPage: 142, endPage: 161 },
  { juz: 9,  surah: "Al-A'raf",     startPage: 162, endPage: 181 },
  { juz: 10, surah: "Al-Anfal",     startPage: 182, endPage: 201 },
  { juz: 11, surah: "At-Taubah",    startPage: 202, endPage: 221 },
  { juz: 12, surah: "Hud",          startPage: 222, endPage: 241 },
  { juz: 13, surah: "Yusuf",        startPage: 242, endPage: 261 },
  { juz: 14, surah: "Al-Hijr",      startPage: 262, endPage: 281 },
  { juz: 15, surah: "Al-Isra'",     startPage: 282, endPage: 301 },
  { juz: 16, surah: "Al-Kahf",      startPage: 302, endPage: 321 },
  { juz: 17, surah: "Al-Anbiya'",   startPage: 322, endPage: 341 },
  { juz: 18, surah: "Al-Mu'minun",  startPage: 342, endPage: 361 },
  { juz: 19, surah: "Al-Furqan",    startPage: 362, endPage: 381 },
  { juz: 20, surah: "An-Naml",      startPage: 382, endPage: 401 },
  { juz: 21, surah: "Al-'Ankabut",  startPage: 402, endPage: 421 },
  { juz: 22, surah: "Al-Ahzab",     startPage: 422, endPage: 441 },
  { juz: 23, surah: "Yasin",        startPage: 442, endPage: 461 },
  { juz: 24, surah: "Az-Zumar",     startPage: 462, endPage: 481 },
  { juz: 25, surah: "Fussilat",     startPage: 482, endPage: 501 },
  { juz: 26, surah: "Al-Ahqaf",     startPage: 502, endPage: 521 },
  { juz: 27, surah: "Az-Zariyat",   startPage: 522, endPage: 541 },
  { juz: 28, surah: "Al-Mujadilah", startPage: 542, endPage: 561 },
  { juz: 29, surah: "Al-Mulk",      startPage: 562, endPage: 581 },
  { juz: 30, surah: "An-Naba'",     startPage: 582, endPage: 604 }
];

const SURAH_META = [
  null,
  ["Al-Fatihah","الفاتحة"],["Al-Baqarah","البقرة"],["Ali 'Imran","آل عمران"],["An-Nisa'","النساء"],
  ["Al-Ma'idah","المائدة"],["Al-An'am","الأنعام"],["Al-A'raf","الأعراف"],["Al-Anfal","الأنفال"],
  ["At-Taubah","التوبة"],["Yunus","يونس"],["Hud","هود"],["Yusuf","يوسف"],
  ["Ar-Ra'd","الرعد"],["Ibrahim","إبراهيم"],["Al-Hijr","الحجر"],["An-Nahl","النحل"],
  ["Al-Isra'","الإسراء"],["Al-Kahf","الكهف"],["Maryam","مريم"],["Taha","طه"],
  ["Al-Anbiya'","الأنبياء"],["Al-Hajj","الحج"],["Al-Mu'minun","المؤمنون"],["An-Nur","النور"],
  ["Al-Furqan","الفرقان"],["Asy-Syu'ara'","الشعراء"],["An-Naml","النمل"],["Al-Qasas","القصص"],
  ["Al-'Ankabut","العنكبوت"],["Ar-Rum","الروم"],["Luqman","لقمان"],["As-Sajdah","السجدة"],
  ["Al-Ahzab","الأحزاب"],["Saba'","سبأ"],["Fatir","فاطر"],["Yasin","يس"],
  ["As-Saffat","الصافات"],["Sad","ص"],["Az-Zumar","الزمر"],["Ghafir","غافر"],
  ["Fussilat","فصلت"],["Asy-Syura","الشورى"],["Az-Zukhruf","الزخرف"],["Ad-Dukhan","الدخان"],
  ["Al-Jasiyah","الجاثية"],["Al-Ahqaf","الأحقاف"],["Muhammad","محمد"],["Al-Fath","الفتح"],
  ["Al-Hujurat","الحجرات"],["Qaf","ق"],["Az-Zariyat","الذاريات"],["At-Tur","الطور"],
  ["An-Najm","النجم"],["Al-Qamar","القمر"],["Ar-Rahman","الرحمن"],["Al-Waqi'ah","الواقعة"],
  ["Al-Hadid","الحديد"],["Al-Mujadilah","المجادلة"],["Al-Hasyr","الحشر"],["Al-Mumtahanah","الممتحنة"],
  ["As-Saff","الصف"],["Al-Jumu'ah","الجمعة"],["Al-Munafiqun","المنافقون"],["At-Taghabun","التغابن"],
  ["At-Talaq","الطلاق"],["At-Tahrim","التحريم"],["Al-Mulk","الملك"],["Al-Qalam","القلم"],
  ["Al-Haqqah","الحاقة"],["Al-Ma'arij","المعارج"],["Nuh","نوح"],["Al-Jinn","الجن"],
  ["Al-Muzzammil","المزمل"],["Al-Muddassir","المدثر"],["Al-Qiyamah","القيامة"],["Al-Insan","الإنسان"],
  ["Al-Mursalat","المرسلات"],["An-Naba'","النبأ"],["An-Nazi'at","النازعات"],["'Abasa","عبس"],
  ["At-Takwir","التكوير"],["Al-Infitar","الانفطار"],["Al-Mutaffifin","المطففين"],["Al-Insyiqaq","الانشقاق"],
  ["Al-Buruj","البروج"],["At-Tariq","الطارق"],["Al-A'la","الأعلى"],["Al-Ghasyiyah","الغاشية"],
  ["Al-Fajr","الفجر"],["Al-Balad","البلد"],["Asy-Syams","الشمس"],["Al-Lail","الليل"],
  ["Ad-Duha","الضحى"],["Asy-Syarh","الشرح"],["At-Tin","التين"],["Al-'Alaq","العلق"],
  ["Al-Qadr","القدر"],["Al-Bayyinah","البينة"],["Az-Zalzalah","الزلزلة"],["Al-'Adiyat","العاديات"],
  ["Al-Qari'ah","القارعة"],["At-Takasur","التكاثر"],["Al-'Asr","العصر"],["Al-Humazah","الهمزة"],
  ["Al-Fil","الفيل"],["Quraisy","قريش"],["Al-Ma'un","الماعون"],["Al-Kausar","الكوثر"],
  ["Al-Kafirun","الكافرون"],["An-Nasr","النصر"],["Al-Lahab","المسد"],["Al-Ikhlas","الإخلاص"],
  ["Al-Falaq","الفلق"],["An-Nas","الناس"]
];

const SURAH_AYAH_COUNTS = [
  0,
  7,286,200,176,120,165,206,75,129,109,
  123,111,43,52,99,128,111,110,98,135,
  112,78,118,64,77,227,93,88,69,60,
  34,30,73,54,45,83,182,88,75,85,
  54,53,89,59,37,35,38,29,18,45,
  60,49,62,55,78,96,29,22,24,13,
  14,11,11,18,12,12,30,52,52,44,
  28,28,20,56,40,31,50,40,46,42,
  29,19,36,25,22,17,19,26,30,20,
  15,21,11,8,8,19,5,8,8,11,
  11,8,3,9,5,4,7,3,6,3,
  5,4,5,6
];

const SURAH_OFFSETS = (() => {
  const arr = [0];
  let acc = 0;
  for (let i = 1; i <= 114; i++) {
    arr[i] = acc;
    acc += SURAH_AYAH_COUNTS[i];
  }
  return arr;
})();

function getGlobalAyahNumber(surahNum, ayahNum) {
  const s = Number(surahNum);
  const a = Number(ayahNum);
  if (s < 1 || s > 114) return 1;
  return SURAH_OFFSETS[s] + a;
}

const QARI_CONFIG = {
  Husary_128kbps: {
    label: 'Syekh Al-Husary',
    islamicEdition: 'ar.husary',
    quranComFolder: 'Husary/mp3',
    everyAyahFolder: 'Husary_128kbps'
  },
  Alafasy_128kbps: {
    label: 'Syekh Mishary Alafasy',
    islamicEdition: 'ar.alafasy',
    quranComFolder: 'Alafasy/mp3',
    everyAyahFolder: 'Alafasy_128kbps'
  },
  Minshawy_Murattal_128kbps: {
    label: 'Syekh Al-Minshawi',
    islamicEdition: 'ar.minshawi',
    quranComFolder: 'Minshawi/Murattal/mp3',
    everyAyahFolder: 'Minshawy_Murattal_128kbps'
  }
};

/* =========================================================================
   2. KOLEKSI KARTU HADIAH
   ========================================================================= */
const GIFT_CARDS = [
  { id: "card_1",  stars: 3,   emoji: "🌱", name: "Tunas Hafizh",      desc: "Langkah pertama menghafal dengan ceria!" },
  { id: "card_2",  stars: 6,   emoji: "🌙", name: "Lentera Malam",     desc: "Murojaah rutin bikin hati tenang." },
  { id: "card_3",  stars: 10,  emoji: "🕌", name: "Sahabat Masjid",    desc: "Semangatmu makin bersinar setiap hari." },
  { id: "card_4",  stars: 15,  emoji: "📖", name: "Pecinta Mushaf",    desc: "Susunan ayat makin kuat dan tidak tertukar." },
  { id: "card_5",  stars: 22,  emoji: "⭐", name: "Bintang Murojaah",  desc: "Keren! Kamu sudah sangat teliti." },
  { id: "card_6",  stars: 30,  emoji: "🐪", name: "Penjelajah Gurun",  desc: "Tangguh menyelesaikan banyak halaman." },
  { id: "card_7",  stars: 40,  emoji: "🌴", name: "Oasis Ilmu",        desc: "Hafalan dan arti ayat makin melekat." },
  { id: "card_8",  stars: 55,  emoji: "🧭", name: "Kompas Kebaikan",   desc: "Ayat Al-Qur'an menjadi penunjuk jalanmu." },
  { id: "card_9",  stars: 70,  emoji: "🏅", name: "Juara Level 2",     desc: "Berani menyusun ayat tanpa bantuan bayangan!" },
  { id: "card_10", stars: 90,  emoji: "🕊️", name: "Hati Damai",        desc: "Istiqamah bersama Al-Qur'an setiap waktu." },
  { id: "card_11", stars: 120, emoji: "🏆", name: "Piala Mutqin",      desc: "Hafalan kuat, rapi, dan lancar luar biasa." },
  { id: "card_12", stars: 160, emoji: "👑", name: "Mahkota Cahaya",    desc: "Hadiah terindah untuk penghafal Al-Qur'an!" }
];

/* =========================================================================
   3. DATA HALAMAN OFFLINE BAWAAN
   ========================================================================= */
const BUILTIN_PAGES = {
  1: {
    page: 1,
    surahLatin: "Al-Fatihah",
    surahArabic: "سورة الفاتحة",
    surahShortArabic: "الفاتحة",
    showBasmalah: false,
    verses: [
      { surahNumber: 1, numberInSurah: 1, surahTag: "Al-Fatihah", arabic: "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ", meaning: "Dengan nama Allah Yang Maha Pengasih lagi Maha Penyayang" },
      { surahNumber: 1, numberInSurah: 2, surahTag: "Al-Fatihah", arabic: "الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ", meaning: "Segala puji bagi Allah Tuhan seluruh alam" },
      { surahNumber: 1, numberInSurah: 3, surahTag: "Al-Fatihah", arabic: "الرَّحْمَٰنِ الرَّحِيمِ", meaning: "Yang Maha Pengasih lagi Maha Penyayang" },
      { surahNumber: 1, numberInSurah: 4, surahTag: "Al-Fatihah", arabic: "مَالِكِ يَوْمِ الدِّينِ", meaning: "Pemilik hari pembalasan" },
      { surahNumber: 1, numberInSurah: 5, surahTag: "Al-Fatihah", arabic: "إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ", meaning: "Hanya kepada-Mu kami menyembah dan hanya kepada-Mu kami memohon pertolongan" },
      { surahNumber: 1, numberInSurah: 6, surahTag: "Al-Fatihah", arabic: "اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ", meaning: "Tunjukkanlah kami jalan yang lurus" },
      { surahNumber: 1, numberInSurah: 7, surahTag: "Al-Fatihah", arabic: "صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ", meaning: "Yaitu jalan orang yang Engkau beri nikmat bukan yang dimurkai dan bukan pula yang sesat" }
    ]
  },

  582: {
    page: 582,
    surahLatin: "An-Naba'",
    surahArabic: "سورة النبأ",
    surahShortArabic: "النبأ",
    showBasmalah: true,
    verses: [
      { surahNumber: 78, numberInSurah: 1,  surahTag: "An-Naba'", arabic: "عَمَّ يَتَسَاءَلُونَ", meaning: "Tentang apakah mereka saling bertanya" },
      { surahNumber: 78, numberInSurah: 2,  surahTag: "An-Naba'", arabic: "عَنِ النَّبَإِ الْعَظِيمِ", meaning: "Tentang berita yang besar" },
      { surahNumber: 78, numberInSurah: 3,  surahTag: "An-Naba'", arabic: "الَّذِي هُمْ فِيهِ مُخْتَلِفُونَ", meaning: "Yang mereka perselisihkan tentangnya" },
      { surahNumber: 78, numberInSurah: 4,  surahTag: "An-Naba'", arabic: "كَلَّا سَيَعْلَمُونَ", meaning: "Tidak sekali-kali kelak mereka akan mengetahui" },
      { surahNumber: 78, numberInSurah: 5,  surahTag: "An-Naba'", arabic: "ثُمَّ كَلَّا سَيَعْلَمُونَ", meaning: "Kemudian kelak mereka pasti akan mengetahui" },
      { surahNumber: 78, numberInSurah: 6,  surahTag: "An-Naba'", arabic: "أَلَمْ نَجْعَلِ الْأَرْضَ مِهَادًا", meaning: "Bukankah Kami telah menjadikan bumi sebagai hamparan" },
      { surahNumber: 78, numberInSurah: 7,  surahTag: "An-Naba'", arabic: "وَالْجِبَالَ أَوْتَادًا", meaning: "Dan gunung-gunung sebagai pasak" },
      { surahNumber: 78, numberInSurah: 8,  surahTag: "An-Naba'", arabic: "وَخَلَقْنَاكُمْ أَزْوَاجًا", meaning: "Dan Kami menciptakan kalian berpasang-pasangan" },
      { surahNumber: 78, numberInSurah: 9,  surahTag: "An-Naba'", arabic: "وَجَعَلْنَا نَوْمَكُمْ سُبَاتًا", meaning: "Dan Kami jadikan tidur kalian untuk istirahat" },
      { surahNumber: 78, numberInSurah: 10, surahTag: "An-Naba'", arabic: "وَجَعَلْنَا اللَّيْلَ لِبَاسًا", meaning: "Dan Kami jadikan malam sebagai pakaian penutup" },
      { surahNumber: 78, numberInSurah: 11, surahTag: "An-Naba'", arabic: "وَجَعَلْنَا النَّهَارَ مَعَاشًا", meaning: "Dan Kami jadikan siang untuk mencari penghidupan" },
      { surahNumber: 78, numberInSurah: 12, surahTag: "An-Naba'", arabic: "وَبَنَيْنَا فَوْقَكُمْ سَبْعًا شِدَادًا", meaning: "Dan Kami bangun di atas kalian tujuh langit yang kokoh" },
      { surahNumber: 78, numberInSurah: 13, surahTag: "An-Naba'", arabic: "وَجَعَلْنَا سِرَاجًا وَهَّاجًا", meaning: "Dan Kami jadikan pelita yang sangat terang" },
      { surahNumber: 78, numberInSurah: 14, surahTag: "An-Naba'", arabic: "وَأَنزَلْنَا مِنَ الْمُعْصِرَاتِ مَاءً ثَجَّاجًا", meaning: "Dan Kami turunkan dari awan air hujan yang sangat deras" },
      { surahNumber: 78, numberInSurah: 15, surahTag: "An-Naba'", arabic: "لِّنُخْرِجَ بِهِ حَبًّا وَنَبَاتًا", meaning: "Agar Kami tumbuhkan dengannya biji-bijian dan tanaman" },
      { surahNumber: 78, numberInSurah: 16, surahTag: "An-Naba'", arabic: "وَجَنَّاتٍ أَلْفَافًا", meaning: "Serta kebun-kebun yang rindang" }
    ]
  },

  604: {
    page: 604,
    surahLatin: "Al-Ikhlas · Al-Falaq · An-Nas",
    surahArabic: "سورة الإخلاص · الفلق · الناس",
    surahShortArabic: "الإخلاص - الناس",
    showBasmalah: true,
    verses: [
      { surahNumber: 112, numberInSurah: 1, surahTag: "Al-Ikhlas", arabic: "قُلْ هُوَ اللَّهُ أَحَدٌ", meaning: "Katakanlah Dialah Allah Yang Maha Esa" },
      { surahNumber: 112, numberInSurah: 2, surahTag: "Al-Ikhlas", arabic: "اللَّهُ الصَّمَدُ", meaning: "Allah tempat bergantung segala sesuatu" },
      { surahNumber: 112, numberInSurah: 3, surahTag: "Al-Ikhlas", arabic: "لَمْ يَلِدْ وَلَمْ يُولَدْ", meaning: "Dia tidak beranak dan tidak pula diperanakkan" },
      { surahNumber: 112, numberInSurah: 4, surahTag: "Al-Ikhlas", arabic: "وَلَمْ يَكُن لَّهُ كُفُوًا أَحَدٌ", meaning: "Dan tidak ada sesuatu pun yang setara dengan-Nya" },
      { surahNumber: 113, numberInSurah: 1, surahTag: "Al-Falaq", arabic: "قُلْ أَعُوذُ بِرَبِّ الْفَلَقِ", meaning: "Katakanlah aku berlindung kepada Tuhan penguasa subuh" },
      { surahNumber: 113, numberInSurah: 2, surahTag: "Al-Falaq", arabic: "مِن شَرِّ مَا خَلَقَ", meaning: "Dari kejahatan makhluk yang Dia ciptakan" },
      { surahNumber: 113, numberInSurah: 3, surahTag: "Al-Falaq", arabic: "وَمِن شَرِّ غَاسِقٍ إِذَا وَقَبَ", meaning: "Dan dari kejahatan malam apabila telah gelap gulita" },
      { surahNumber: 113, numberInSurah: 4, surahTag: "Al-Falaq", arabic: "وَمِن شَرِّ النَّفَّاثَاتِ فِي الْعُقَدِ", meaning: "Dan dari kejahatan penyihir yang meniup pada buhul tali" },
      { surahNumber: 113, numberInSurah: 5, surahTag: "Al-Falaq", arabic: "وَمِن شَرِّ حَاسِدٍ إِذَا حَسَدَ", meaning: "Dan dari kejahatan orang yang dengki apabila ia dengki" },
      { surahNumber: 114, numberInSurah: 1, surahTag: "An-Nas", arabic: "قُلْ أَعُوذُ بِرَبِّ النَّاسِ", meaning: "Katakanlah aku berlindung kepada Tuhan manusia" },
      { surahNumber: 114, numberInSurah: 2, surahTag: "An-Nas", arabic: "مَلِكِ النَّاسِ", meaning: "Raja seluruh manusia" },
      { surahNumber: 114, numberInSurah: 3, surahTag: "An-Nas", arabic: "إِلَٰهِ النَّاسِ", meaning: "Sembahan seluruh manusia" },
      { surahNumber: 114, numberInSurah: 4, surahTag: "An-Nas", arabic: "مِن شَرِّ الْوَسْوَاسِ الْخَنَّاسِ", meaning: "Dari kejahatan bisikan setan yang bersembunyi" },
      { surahNumber: 114, numberInSurah: 5, surahTag: "An-Nas", arabic: "الَّذِي يُوَسْوِسُ فِي صُدُورِ النَّاسِ", meaning: "Yang membisikkan kejahatan ke dalam dada manusia" },
      { surahNumber: 114, numberInSurah: 6, surahTag: "An-Nas", arabic: "مِنَ الْجِنَّةِ وَالنَّاسِ", meaning: "Dari golongan jin dan manusia" }
    ]
  }
};

/* =========================================================================
   4. STATE APLIKASI & PENYIMPANAN PROGRES (TETAP MENJAGA PROGRES LAMA)
   ========================================================================= */
const STORAGE_KEY = 'alquran_go_state_v1';

const DEFAULT_STATE = {
  username: 'Sahabat Quran',
  avatar: '🧒',
  soundOn: true,
  autoMurottal: true,
  qari: 'Husary_128kbps',
  mode: 'ayat',
  level: 1,
  progress: {},
  unlockedGifts: []
};

const AppState = loadState();

function loadState() {
  try {
    const raw =
      localStorage.getItem(STORAGE_KEY) ||
      localStorage.getItem('alquran_play_state_v2') ||
      localStorage.getItem('alquran_play_state_v1');
    if (!raw) return { ...DEFAULT_STATE, progress: {}, unlockedGifts: [] };
    const parsed = JSON.parse(raw);
    return {
      ...DEFAULT_STATE,
      ...parsed,
      progress: parsed.progress || {},
      unlockedGifts: Array.isArray(parsed.unlockedGifts) ? parsed.unlockedGifts : []
    };
  } catch (e) {
    return { ...DEFAULT_STATE, progress: {}, unlockedGifts: [] };
  }
}

function saveState() {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(AppState));
  } catch (e) {
    console.warn('Gagal menyimpan progres:', e);
  }
}

function getProgressKey(page, mode = AppState.mode, level = AppState.level) {
  return `${mode}_L${level}_P${page}`;
}

function getPageStars(page, mode = AppState.mode, level = AppState.level) {
  return AppState.progress[getProgressKey(page, mode, level)] || 0;
}

function getTotalStars() {
  return Object.values(AppState.progress).reduce((sum, val) => sum + Number(val || 0), 0);
}

function getJuzCompletedCount(juzObj, mode = AppState.mode, level = AppState.level) {
  let done = 0;
  for (let p = juzObj.startPage; p <= juzObj.endPage; p++) {
    if (getPageStars(p, mode, level) > 0) done++;
  }
  return done;
}

function checkNewGiftUnlocks() {
  const totalStars = getTotalStars();
  const newlyUnlocked = [];
  GIFT_CARDS.forEach(card => {
    if (totalStars >= card.stars && !AppState.unlockedGifts.includes(card.id)) {
      AppState.unlockedGifts.push(card.id);
      newlyUnlocked.push(card);
    }
  });
  if (newlyUnlocked.length > 0) saveState();
  return newlyUnlocked;
}

/* =========================================================================
   5. INDEXEDDB TEKS 604 HALAMAN (PageDB) & AUDIO MP3 BLOB OFFLINE (AudioDB)
   ========================================================================= */
const PageDB = {
  dbName: 'alquran_play_pages_db',
  storeName: 'pages',
  db: null,

  open() {
    if (this.db) return Promise.resolve(this.db);
    return new Promise((resolve) => {
      if (!('indexedDB' in window)) { resolve(null); return; }
      const req = indexedDB.open(this.dbName, 1);
      req.onupgradeneeded = (e) => {
        const db = e.target.result;
        if (!db.objectStoreNames.contains(this.storeName)) {
          db.createObjectStore(this.storeName, { keyPath: 'page' });
        }
      };
      req.onsuccess = (e) => { this.db = e.target.result; resolve(this.db); };
      req.onerror = () => resolve(null);
    });
  },

  async getAllMeta() {
    const db = await this.open();
    if (!db) return new Map();
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readonly');
      const map = new Map();
      const req = tx.objectStore(this.storeName).openCursor();
      req.onsuccess = (e) => {
        const cursor = e.target.result;
        if (cursor) {
          map.set(Number(cursor.key), cursor.value.surahLatin || `Halaman ${cursor.key}`);
          cursor.continue();
        } else {
          resolve(map);
        }
      };
      req.onerror = () => resolve(map);
    });
  },

  async getPage(pageNum) {
    const db = await this.open();
    if (!db) return null;
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readonly');
      const req = tx.objectStore(this.storeName).get(Number(pageNum));
      req.onsuccess = () => resolve(req.result || null);
      req.onerror = () => resolve(null);
    });
  },

  async putPage(pageObj) {
    const db = await this.open();
    if (!db) return false;
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readwrite');
      tx.objectStore(this.storeName).put(pageObj);
      tx.oncomplete = () => resolve(true);
      tx.onerror = () => resolve(false);
    });
  },

  async putManyPages(pageArray) {
    const db = await this.open();
    if (!db) return false;
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readwrite');
      const store = tx.objectStore(this.storeName);
      pageArray.forEach(p => store.put(p));
      tx.oncomplete = () => resolve(true);
      tx.onerror = () => resolve(false);
    });
  }
};

const AudioDB = {
  dbName: 'alquran_go_audio_db',
  storeName: 'audio',
  db: null,
  keysSet: new Set(), // Cache cepat di RAM: `${qari}_${surah}_${ayah}`

  makeKey(qari, surahNum, ayahNum) {
    return `${qari}_${Number(surahNum)}_${Number(ayahNum)}`;
  },

  open() {
    if (this.db) return Promise.resolve(this.db);
    return new Promise((resolve) => {
      if (!('indexedDB' in window)) { resolve(null); return; }
      const req = indexedDB.open(this.dbName, 1);
      req.onupgradeneeded = (e) => {
        const db = e.target.result;
        if (!db.objectStoreNames.contains(this.storeName)) {
          db.createObjectStore(this.storeName, { keyPath: 'id' });
        }
      };
      req.onsuccess = (e) => { this.db = e.target.result; resolve(this.db); };
      req.onerror = () => resolve(null);
    });
  },

  async init() {
    const db = await this.open();
    if (!db) return;
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readonly');
      const req = tx.objectStore(this.storeName).getAllKeys();
      req.onsuccess = () => {
        this.keysSet = new Set(req.result || []);
        resolve();
      };
      req.onerror = () => resolve();
    });
  },

  has(qari, surahNum, ayahNum) {
    return this.keysSet.has(this.makeKey(qari, surahNum, ayahNum));
  },

  countByQari(qari) {
    const prefix = `${qari}_`;
    let count = 0;
    this.keysSet.forEach(k => { if (k.startsWith(prefix)) count++; });
    return count;
  },

  countTotal() {
    return this.keysSet.size;
  },

  async getRecord(qari, surahNum, ayahNum) {
    const db = await this.open();
    if (!db) return null;
    const id = this.makeKey(qari, surahNum, ayahNum);
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readonly');
      const req = tx.objectStore(this.storeName).get(id);
      req.onsuccess = () => resolve(req.result || null);
      req.onerror = () => resolve(null);
    });
  },

  async putBlob(qari, surahNum, ayahNum, blob) {
    const db = await this.open();
    if (!db) return false;
    const id = this.makeKey(qari, surahNum, ayahNum);
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readwrite');
      tx.objectStore(this.storeName).put({
        id,
        qari,
        surah: Number(surahNum),
        ayah: Number(ayahNum),
        blob
      });
      tx.oncomplete = () => {
        this.keysSet.add(id);
        resolve(true);
      };
      tx.onerror = () => resolve(false);
    });
  },

  async putOpaqueMarker(qari, surahNum, ayahNum, opaqueUrl) {
    const db = await this.open();
    if (!db) return false;
    const id = this.makeKey(qari, surahNum, ayahNum);
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readwrite');
      tx.objectStore(this.storeName).put({
        id,
        qari,
        surah: Number(surahNum),
        ayah: Number(ayahNum),
        blob: null,
        opaqueUrl
      });
      tx.oncomplete = () => {
        this.keysSet.add(id);
        resolve(true);
      };
      tx.onerror = () => resolve(false);
    });
  },

  async clearAll() {
    if ('caches' in window) {
      try { await caches.delete('alquran-go-audio-v5'); } catch (e) {}
    }
    const db = await this.open();
    if (!db) return false;
    return new Promise((resolve) => {
      const tx = db.transaction(this.storeName, 'readwrite');
      tx.objectStore(this.storeName).clear();
      tx.oncomplete = () => {
        this.keysSet.clear();
        resolve(true);
      };
      tx.onerror = () => resolve(false);
    });
  }
};

/* =========================================================================
   6. EFEK SUARA CERIA (WEB AUDIO API - 100% OFFLINE)
   ========================================================================= */
const SoundFX = {
  ctx: null,

  init() {
    if (!this.ctx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (AudioCtx) this.ctx = new AudioCtx();
    }
    if (this.ctx && this.ctx.state === 'suspended') this.ctx.resume();
  },

  playTone(freq, duration = 0.12, type = 'sine', volume = 0.14, delay = 0) {
    if (!AppState.soundOn) return;
    this.init();
    if (!this.ctx) return;

    const now = this.ctx.currentTime + delay;
    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();
    osc.type = type;
    osc.frequency.setValueAtTime(freq, now);
    gain.gain.setValueAtTime(0.001, now);
    gain.gain.exponentialRampToValueAtTime(volume, now + 0.015);
    gain.gain.exponentialRampToValueAtTime(0.001, now + duration);
    osc.connect(gain);
    gain.connect(this.ctx.destination);
    osc.start(now);
    osc.stop(now + duration + 0.02);
  },

  tap() { this.playTone(520, 0.06, 'triangle', 0.1); },
  correct() {
    this.playTone(587.33, 0.11, 'sine', 0.14, 0);
    this.playTone(880.00, 0.16, 'sine', 0.15, 0.07);
  },
  verseComplete() {
    this.playTone(659.25, 0.1, 'sine', 0.13, 0);
    this.playTone(880.00, 0.12, 'sine', 0.13, 0.08);
    this.playTone(1046.50, 0.18, 'sine', 0.14, 0.16);
  },
  wrong() {
    this.playTone(230, 0.14, 'sawtooth', 0.08, 0);
    this.playTone(180, 0.18, 'sawtooth', 0.08, 0.09);
  },
  hint() {
    this.playTone(740, 0.09, 'triangle', 0.12, 0);
    this.playTone(987, 0.14, 'triangle', 0.12, 0.08);
  },
  win() {
    [523.25, 659.25, 783.99, 1046.50].forEach((n, idx) => {
      this.playTone(n, 0.22, 'triangle', 0.16, idx * 0.1);
    });
  }
};

/* =========================================================================
   7. PEMUTAR & PENGUNDUH AUDIO MUROTTAL OFFLINE (INDEXEDDB BLOB)
   ========================================================================= */
const MurottalPlayer = {
  audio: new Audio(),
  activeVerseIndex: -1,
  lastPlayedVerseIndex: -1,
  currentObjectUrl: null,
  playToken: 0,
  downloadAbortCtrl: null,
  proxyAvailable: null,

  init() {
    this.audio.preload = 'auto';
    this.audio.addEventListener('ended', () => this.clearVisualState());
    this.audio.addEventListener('error', () => {
      this.clearVisualState();
      if (!navigator.onLine) {
        showToast('Audio ayat ini belum diunduh offline. Tekan "⬇️ Audio Hal" saat online.');
      }
    });
  },

  async checkProxy() {
    if (this.proxyAvailable !== null) return this.proxyAvailable;
    if (location.protocol === 'file:') {
      this.proxyAvailable = false;
      return false;
    }
    try {
      const controller = new AbortController();
      const timer = setTimeout(() => controller.abort(), 4500);
      const resp = await fetch('./audio_proxy.php?ping=1', { cache: 'no-store', signal: controller.signal });
      clearTimeout(timer);
      if (resp.ok) {
        const ct = resp.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
          const data = await resp.json();
          if (data && data.ok === true) {
            this.proxyAvailable = true;
            return true;
          }
        }
      }
    } catch (e) {}
    this.proxyAvailable = false;
    return false;
  },

  getQariCfg(qariKey = AppState.qari) {
    return QARI_CONFIG[qariKey] || QARI_CONFIG.Husary_128kbps;
  },

  getProxyAudioUrl(qariKey, surahNum, ayahNum) {
    const q = encodeURIComponent(qariKey || 'Husary_128kbps');
    return `./audio_proxy.php?qari=${q}&s=${Number(surahNum)}&a=${Number(ayahNum)}`;
  },

  getCorsAudioUrls(qariKey, surahNum, ayahNum) {
    const cfg = this.getQariCfg(qariKey);
    const globalId = getGlobalAyahNumber(surahNum, ayahNum);
    const sss = String(surahNum).padStart(3, '0');
    const aaa = String(ayahNum).padStart(3, '0');
    return [
      `https://cdn.islamic.network/quran/audio/128/${cfg.islamicEdition}/${globalId}.mp3`,
      `https://cdn.islamic.network/quran/audio/64/${cfg.islamicEdition}/${globalId}.mp3`,
      `https://audio.qurancdn.com/${cfg.quranComFolder}/${sss}${aaa}.mp3`,
      `https://verses.quran.com/${cfg.quranComFolder}/${sss}${aaa}.mp3`,
      `https://everyayah.com/data/${cfg.everyAyahFolder}/${sss}${aaa}.mp3`
    ];
  },

  getDirectStreamUrl(qariKey, surahNum, ayahNum) {
    const cfg = this.getQariCfg(qariKey);
    const sss = String(surahNum).padStart(3, '0');
    const aaa = String(ayahNum).padStart(3, '0');
    return `https://everyayah.com/data/${cfg.everyAyahFolder}/${sss}${aaa}.mp3`;
  },

  async fetchBlobFromUrl(url, isSameOrigin, signal = null) {
    const opts = isSameOrigin ? { signal } : { mode: 'cors', signal };
    const resp = await fetch(url, opts);
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    const ct = (resp.headers.get('content-type') || '').toLowerCase();
    if (ct.includes('text/html') || ct.includes('application/json')) {
      throw new Error('Bukan file audio');
    }
    const blob = await resp.blob();
    if (!blob || blob.size < 800) throw new Error('Audio terlalu kecil');
    return blob.type && blob.type.includes('audio')
      ? blob
      : new Blob([blob], { type: 'audio/mpeg' });
  },

  async fetchAudioBlob(qariKey, surahNum, ayahNum, signal = null) {
    // 1. Utamakan same-origin audio_proxy.php (bebas blokir CORS di hosting PHP)
    const hasProxy = await this.checkProxy();
    if (hasProxy) {
      try {
        return await this.fetchBlobFromUrl(
          this.getProxyAudioUrl(qariKey, surahNum, ayahNum),
          true,
          signal
        );
      } catch (err) {
        if (err && err.name === 'AbortError') throw err;
      }
    }

    // 2. Fallback langsung ke CDN CORS (untuk hosting statis tanpa PHP)
    const urls = this.getCorsAudioUrls(qariKey, surahNum, ayahNum);
    for (const url of urls) {
      try {
        return await this.fetchBlobFromUrl(url, false, signal);
      } catch (err) {
        if (err && err.name === 'AbortError') throw err;
      }
    }

    throw new Error(`Gagal mengunduh audio ${surahNum}:${ayahNum}`);
  },

  // 3. Fallback terakhir untuk hosting statis jika semua CDN blokir CORS: simpan opaque ke Cache API
  async cacheOpaqueFallback(qariKey, surahNum, ayahNum, signal = null) {
    if (!('caches' in window)) return false;
    const url = this.getDirectStreamUrl(qariKey, surahNum, ayahNum);
    try {
      const resp = await fetch(url, { mode: 'no-cors', signal });
      if (resp && (resp.ok || resp.type === 'opaque')) {
        const cache = await caches.open('alquran-go-audio-v5');
        await cache.put(url, resp);
        await AudioDB.putOpaqueMarker(qariKey, surahNum, ayahNum, url);
        return true;
      }
    } catch (err) {
      if (err && err.name === 'AbortError') throw err;
    }
    return false;
  },

  async ensureAudioCached(qariKey, surahNum, ayahNum, signal = null) {
    if (AudioDB.has(qariKey, surahNum, ayahNum)) return true;
    try {
      const blob = await this.fetchAudioBlob(qariKey, surahNum, ayahNum, signal);
      await AudioDB.putBlob(qariKey, surahNum, ayahNum, blob);
      return true;
    } catch (err) {
      if (err && err.name === 'AbortError') throw err;
      const okOpaque = await this.cacheOpaqueFallback(qariKey, surahNum, ayahNum, signal);
      if (okOpaque) return true;
      throw err;
    }
  },

  revokeObjectUrl() {
    if (this.currentObjectUrl) {
      try { URL.revokeObjectURL(this.currentObjectUrl); } catch (e) {}
      this.currentObjectUrl = null;
    }
  },

  clearVisualState() {
    this.activeVerseIndex = -1;
    const bar = document.getElementById('murottal-bar');
    if (bar) bar.classList.add('hidden');

    document.querySelectorAll('.mushaf-word.verse-reciting').forEach(el => el.classList.remove('verse-reciting'));
    document.querySelectorAll('.ayah-end-badge.playing').forEach(el => el.classList.remove('playing'));
    document.querySelectorAll('.arti-verse-card.reciting-verse').forEach(el => el.classList.remove('reciting-verse'));
    document.querySelectorAll('.arti-play-btn.playing').forEach(el => {
      el.classList.remove('playing');
      el.textContent = '🔊';
    });
  },

  stop() {
    this.playToken++;
    try {
      this.audio.pause();
      this.audio.currentTime = 0;
    } catch (e) {}
    this.revokeObjectUrl();
    this.clearVisualState();
  },

  async playVerse(verseIndex, isManual = false) {
    if (!AppState.soundOn && !isManual) return;
    if (!GameSession.pageData || !GameSession.pageData.verses) return;

    const verse = GameSession.pageData.verses[verseIndex];
    if (!verse || !verse.surahNumber) return;

    if (isManual && this.activeVerseIndex === verseIndex && !this.audio.paused) {
      this.stop();
      return;
    }

    this.stop();
    const myToken = ++this.playToken;
    this.activeVerseIndex = verseIndex;
    this.lastPlayedVerseIndex = verseIndex;

    const qariKey = AppState.qari || 'Husary_128kbps';
    const bar = document.getElementById('murottal-bar');
    const statusText = document.getElementById('murottal-status-text');
    const labelSurah = verse.surahTag || GameSession.pageData.surahLatin;

    // 1. Cek IndexedDB terlebih dahulu (100% OFFLINE & INSTAN)
    const rec = await AudioDB.getRecord(qariKey, verse.surahNumber, verse.numberInSurah);
    if (myToken !== this.playToken) return;

    let blob = rec && rec.blob ? rec.blob : null;
    let opaqueUrl = rec && rec.opaqueUrl ? rec.opaqueUrl : null;
    let isOfflineSource = !!(blob || opaqueUrl);

    // 2. Jika belum ada di perangkat dan sedang online, unduh Blob & simpan otomatis agar lain kali offline
    if (!blob && !opaqueUrl && navigator.onLine) {
      if (statusText) statusText.textContent = `Memuat ${labelSurah} · Ayat ${verse.numberInSurah}...`;
      if (bar) bar.classList.remove('hidden');
      try {
        blob = await this.fetchAudioBlob(qariKey, verse.surahNumber, verse.numberInSurah);
        if (myToken !== this.playToken) return;
        await AudioDB.putBlob(qariKey, verse.surahNumber, verse.numberInSurah, blob);
        isOfflineSource = true;
        updatePageAudioButton();
        updateHeaderUI();
      } catch (e) {
        blob = null;
      }
    }

    if (myToken !== this.playToken) return;

    if (!blob && !opaqueUrl && !navigator.onLine) {
      this.clearVisualState();
      showToast('Audio ayat ini belum diunduh offline. Ketuk "⬇️ Audio Hal" saat ada internet.');
      return;
    }

    // Tampilkan sorotan visual & mini-bar
    if (statusText) {
      statusText.textContent = `${isOfflineSource ? '⚡ ' : ''}Murottal ${labelSurah} · Ayat ${verse.numberInSurah}`;
    }
    if (bar) bar.classList.remove('hidden');

    document.querySelectorAll(`.mushaf-word[data-verse-index="${verseIndex}"]`).forEach(el => {
      if (el.classList.contains('solved')) el.classList.add('verse-reciting');
    });
    const badge = document.querySelector(`.ayah-end-badge[data-verse-index="${verseIndex}"]`);
    if (badge) badge.classList.add('playing');

    const artiCard = document.getElementById(`arti-verse-card-${verseIndex}`);
    if (artiCard) artiCard.classList.add('reciting-verse');
    const artiBtn = document.querySelector(`.arti-play-btn[data-verse-index="${verseIndex}"]`);
    if (artiBtn) {
      artiBtn.classList.add('playing');
      artiBtn.textContent = '⏹';
    }

    // Putar dari Blob lokal (IndexedDB), Cache API, atau fallback stream online
    if (blob) {
      this.revokeObjectUrl();
      this.currentObjectUrl = URL.createObjectURL(blob);
      this.audio.src = this.currentObjectUrl;
    } else if (opaqueUrl) {
      this.audio.src = opaqueUrl;
    } else {
      this.audio.src = this.getDirectStreamUrl(qariKey, verse.surahNumber, verse.numberInSurah);
    }

    const playPromise = this.audio.play();
    if (playPromise && typeof playPromise.catch === 'function') {
      playPromise.catch(() => this.clearVisualState());
    }
  },

  replayLast() {
    if (this.lastPlayedVerseIndex >= 0) this.playVerse(this.lastPlayedVerseIndex, true);
  },

  cancelDownload() {
    if (this.downloadAbortCtrl) {
      this.downloadAbortCtrl.abort();
      this.downloadAbortCtrl = null;
    }
  },

  // Unduh daftar ayat [{ surahNumber, numberInSurah }] ke IndexedDB dengan concurrency 3
  async downloadVersesBatch(versesList, onProgress) {
    const qariKey = AppState.qari || 'Husary_128kbps';
    // Hilangkan duplikat
    const uniqueMap = new Map();
    versesList.forEach(v => {
      if (v && v.surahNumber && v.numberInSurah) {
        uniqueMap.set(`${v.surahNumber}:${v.numberInSurah}`, {
          surahNumber: Number(v.surahNumber),
          numberInSurah: Number(v.numberInSurah)
        });
      }
    });
    const uniqueList = Array.from(uniqueMap.values());
    const total = uniqueList.length;
    if (total === 0) return { ok: 0, total: 0, aborted: false };

    this.cancelDownload();
    const ctrl = new AbortController();
    this.downloadAbortCtrl = ctrl;

    let done = 0;
    let ok = 0;
    let aborted = false;
    const batchSize = 3;

    for (let i = 0; i < total; i += batchSize) {
      if (ctrl.signal.aborted) { aborted = true; break; }
      const slice = uniqueList.slice(i, i + batchSize);
      await Promise.all(
        slice.map(async (item) => {
          try {
            await this.ensureAudioCached(qariKey, item.surahNumber, item.numberInSurah, ctrl.signal);
            ok++;
          } catch (err) {
            if (err && err.name === 'AbortError') aborted = true;
          } finally {
            done++;
            if (typeof onProgress === 'function' && !aborted) {
              onProgress(done, total, ok);
            }
          }
        })
      );
      if (aborted) break;
    }

    if (this.downloadAbortCtrl === ctrl) this.downloadAbortCtrl = null;
    return { ok, total, aborted };
  }
};

/* =========================================================================
   8. HELPER TEKS & PEMUAT 30 JUZ (DUAL-API FALLBACK + INDEXEDDB)
   ========================================================================= */
function toArabicDigits(num) {
  const digits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
  return String(num).replace(/\d/g, d => digits[Number(d)]);
}

function shuffleArray(arr) {
  const copy = [...arr];
  for (let i = copy.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [copy[i], copy[j]] = [copy[j], copy[i]];
  }
  return copy;
}

function cleanTranslationText(raw) {
  return String(raw || '')
    .replace(/<sup[^>]*>[\s\S]*?<\/sup>/gi, '')
    .replace(/<[^>]+>/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}

function tokenizeArabicVerse(text) {
  const rawTokens = text.trim().split(/\s+/).filter(Boolean);
  const waqafOnly = /^[\u06D6-\u06ED\u06DE]+$/;
  const merged = [];
  rawTokens.forEach(tok => {
    if (waqafOnly.test(tok) && merged.length > 0) {
      merged[merged.length - 1] += ' ' + tok;
    } else {
      merged.push(tok);
    }
  });
  return merged;
}

function tokenizeMeaning(meaningText) {
  const clean = meaningText.replace(/["“”()\[\]]/g, '').replace(/\s+/g, ' ').trim();
  const words = clean.split(' ').filter(Boolean);
  if (words.length <= 4) return words;

  const chunks = [];
  let i = 0;
  while (i < words.length) {
    const remaining = words.length - i;
    if (remaining === 3) {
      chunks.push(words.slice(i, i + 3).join(' '));
      break;
    } else if (remaining <= 2) {
      chunks.push(words.slice(i).join(' '));
      break;
    } else {
      chunks.push(words.slice(i, i + 2).join(' '));
      i += 2;
    }
  }
  return chunks;
}

function stripLeadingBasmalah(arabicText, surahNumber, ayahInSurah) {
  if (surahNumber === 1 || surahNumber === 9 || ayahInSurah !== 1) {
    return arabicText.trim();
  }
  const tokens = arabicText.trim().split(/\s+/);
  if (tokens.length > 4) {
    const norm = (s) => s
      .replace(/[\u0610-\u061A\u064B-\u065F\u0670\u06D6-\u06ED]/g, '')
      .replace(/[إأآٱ]/g, 'ا');
    if (tokens.slice(0, 4).map(norm).join(' ') === 'بسم الله الرحمن الرحيم') {
      return tokens.slice(4).join(' ').trim();
    }
  }
  return arabicText.trim();
}

async function fetchJsonWithTimeout(url, timeoutMs = 15000) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), timeoutMs);
  try {
    const resp = await fetch(url, { signal: controller.signal });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    return await resp.json();
  } finally {
    clearTimeout(timer);
  }
}

function buildPageObject(pageNum, verses) {
  const uniqueLatin = [];
  verses.forEach(v => {
    const label = v.surahTag || (SURAH_META[v.surahNumber] ? SURAH_META[v.surahNumber][0] : "Al-Qur'an");
    if (!uniqueLatin.includes(label)) uniqueLatin.push(label);
  });

  const first = verses[0] || { surahNumber: 1, numberInSurah: 1 };
  const meta = SURAH_META[first.surahNumber] || ["Al-Qur'an", "القرآن الكريم"];
  const showBasmalah = first.numberInSurah === 1 && first.surahNumber !== 1 && first.surahNumber !== 9;

  return {
    page: Number(pageNum),
    surahLatin: uniqueLatin.slice(0, 2).join(' · '),
    surahArabic: `سورة ${meta[1]}`,
    surahShortArabic: meta[1],
    showBasmalah,
    verses
  };
}

const QuranLoader = {
  offlineMeta: new Map(),

  async init() {
    Object.values(BUILTIN_PAGES).forEach(p => {
      this.offlineMeta.set(Number(p.page), p.surahLatin);
    });
    const dbMeta = await PageDB.getAllMeta();
    dbMeta.forEach((label, pageNum) => {
      this.offlineMeta.set(Number(pageNum), label);
    });
  },

  isPageOffline(pageNum) { return this.offlineMeta.has(Number(pageNum)); },
  getOfflineTitle(pageNum) { return this.offlineMeta.get(Number(pageNum)) || null; },
  countOfflinePages() { return this.offlineMeta.size; },

  async fetchPageFromAlQuranCloud(pageNum) {
    const [arJson, idJson] = await Promise.all([
      fetchJsonWithTimeout(`https://api.alquran.cloud/v1/page/${pageNum}/quran-uthmani`, 12000),
      fetchJsonWithTimeout(`https://api.alquran.cloud/v1/page/${pageNum}/id.indonesian`, 12000)
    ]);

    const arAyahs = arJson && arJson.data && arJson.data.ayahs ? arJson.data.ayahs : [];
    const idAyahs = idJson && idJson.data && idJson.data.ayahs ? idJson.data.ayahs : [];
    if (arAyahs.length === 0) throw new Error('Data AlQuran Cloud kosong');

    const verses = arAyahs.map((ar, idx) => {
      const idItem = idAyahs[idx] || {};
      const sNum = ar.surah ? Number(ar.surah.number) : 1;
      const meta = SURAH_META[sNum] || [ar.surah ? ar.surah.englishName : "Al-Qur'an", "القرآن"];
      return {
        surahNumber: sNum,
        numberInSurah: Number(ar.numberInSurah),
        surahTag: meta[0],
        arabic: stripLeadingBasmalah(ar.text || '', sNum, Number(ar.numberInSurah)),
        meaning: cleanTranslationText(idItem.text || 'Arti ayat belum tersedia')
      };
    });

    return buildPageObject(pageNum, verses);
  },

  async fetchPageFromQuranCom(pageNum) {
    const url = `https://api.quran.com/api/v4/verses/by_page/${pageNum}?language=id&translations=33&fields=text_uthmani&per_page=50`;
    const json = await fetchJsonWithTimeout(url, 12000);
    const list = json && Array.isArray(json.verses) ? json.verses : [];
    if (list.length === 0) throw new Error('Data Quran.com kosong');

    const verses = list.map(v => {
      const [sStr, aStr] = String(v.verse_key || '1:1').split(':');
      const sNum = Number(sStr) || 1;
      const aNum = Number(aStr) || 1;
      const meta = SURAH_META[sNum] || ["Al-Qur'an", "القرآن"];
      const tr = Array.isArray(v.translations) && v.translations[0] ? v.translations[0].text : '';
      return {
        surahNumber: sNum,
        numberInSurah: aNum,
        surahTag: meta[0],
        arabic: stripLeadingBasmalah(v.text_uthmani || '', sNum, aNum),
        meaning: cleanTranslationText(tr || 'Arti ayat belum tersedia')
      };
    });

    return buildPageObject(pageNum, verses);
  },

  async loadPage(pageNum) {
    const pNum = Number(pageNum);
    if (BUILTIN_PAGES[pNum]) return BUILTIN_PAGES[pNum];

    const fromDB = await PageDB.getPage(pNum);
    if (fromDB && Array.isArray(fromDB.verses) && fromDB.verses.length > 0) {
      this.offlineMeta.set(pNum, fromDB.surahLatin);
      return fromDB;
    }

    let pageData = null;
    try {
      pageData = await this.fetchPageFromAlQuranCloud(pNum);
    } catch (e1) {
      pageData = await this.fetchPageFromQuranCom(pNum);
    }

    await PageDB.putPage(pageData);
    this.offlineMeta.set(pNum, pageData.surahLatin);
    return pageData;
  },

  async downloadJuzOffline(juzNumber, onProgress) {
    const juzObj = JUZ_LIST.find(j => j.juz === juzNumber);
    if (!juzObj) return { ok: 0, total: 0, pages: [] };

    const total = juzObj.endPage - juzObj.startPage + 1;
    let ok = 0;
    const pages = [];

    for (let p = juzObj.startPage; p <= juzObj.endPage; p++) {
      try {
        const pd = await this.loadPage(p);
        pages.push(pd);
        ok++;
      } catch (e) {
        console.warn('Gagal mengunduh halaman', p, e);
      }
      if (typeof onProgress === 'function') onProgress(ok, total, p);
    }
    return { ok, total, pages };
  },

  async downloadAll30Juz(onProgress) {
    try {
      if (typeof onProgress === 'function') onProgress(10, 'Mengunduh teks Arab 30 Juz...');
      const arJson = await fetchJsonWithTimeout('https://api.alquran.cloud/v1/quran/quran-uthmani', 45000);
      if (!arJson || !arJson.data || !Array.isArray(arJson.data.surahs)) {
        throw new Error('Gagal mengunduh teks Arab 30 Juz');
      }

      if (typeof onProgress === 'function') onProgress(48, 'Mengunduh terjemahan Indonesia 30 Juz...');
      const idJson = await fetchJsonWithTimeout('https://api.alquran.cloud/v1/quran/id.indonesian', 45000);
      if (!idJson || !idJson.data || !Array.isArray(idJson.data.surahs)) {
        throw new Error('Gagal mengunduh terjemahan 30 Juz');
      }

      if (typeof onProgress === 'function') onProgress(78, 'Menyusun 604 halaman mushaf...');
      const pagesMap = new Map();
      const arSurahs = arJson.data.surahs;
      const idSurahs = idJson.data.surahs;

      for (let sIdx = 0; sIdx < arSurahs.length; sIdx++) {
        const arS = arSurahs[sIdx];
        const idS = idSurahs[sIdx] || { ayahs: [] };
        const sNum = Number(arS.number);
        const meta = SURAH_META[sNum] || [arS.englishName || "Al-Qur'an", "القرآن"];

        for (let aIdx = 0; aIdx < arS.ayahs.length; aIdx++) {
          const arA = arS.ayahs[aIdx];
          const idA = (idS.ayahs && idS.ayahs[aIdx]) ? idS.ayahs[aIdx] : {};
          const pNum = Number(arA.page);
          if (!pNum || pNum < 1 || pNum > 604) continue;

          if (!pagesMap.has(pNum)) pagesMap.set(pNum, []);
          pagesMap.get(pNum).push({
            surahNumber: sNum,
            numberInSurah: Number(arA.numberInSurah),
            surahTag: meta[0],
            arabic: stripLeadingBasmalah(arA.text || '', sNum, Number(arA.numberInSurah)),
            meaning: cleanTranslationText(idA.text || 'Arti ayat belum tersedia')
          });
        }
      }

      const allPages = [];
      pagesMap.forEach((verses, pNum) => {
        if (verses.length > 0) allPages.push(buildPageObject(pNum, verses));
      });

      if (typeof onProgress === 'function') onProgress(92, `Menyimpan ${allPages.length} halaman ke perangkat...`);
      const saved = await PageDB.putManyPages(allPages);
      if (!saved) throw new Error('Gagal menyimpan ke IndexedDB');

      allPages.forEach(p => this.offlineMeta.set(p.page, p.surahLatin));
      if (typeof onProgress === 'function') onProgress(100, `Selesai! ${allPages.length} halaman siap offline.`);
      return allPages.length;
    } catch (bulkErr) {
      let ok = 0;
      const batchSize = 5;
      for (let start = 1; start <= 604; start += batchSize) {
        const batch = [];
        for (let p = start; p < start + batchSize && p <= 604; p++) {
          if (this.isPageOffline(p)) {
            ok++;
          } else {
            batch.push(
              this.fetchPageFromQuranCom(p)
                .then(async (pageData) => {
                  await PageDB.putPage(pageData);
                  this.offlineMeta.set(p, pageData.surahLatin);
                  ok++;
                })
                .catch(() => {})
            );
          }
        }
        if (batch.length > 0) await Promise.all(batch);
        const pct = Math.round((Math.min(start + batchSize - 1, 604) / 604) * 100);
        if (typeof onProgress === 'function') {
          onProgress(pct, `Server cadangan: ${ok}/604 halaman tersimpan...`);
        }
      }
      if (ok < 10) throw bulkErr;
      return ok;
    }
  }
};

/* =========================================================================
   9. MESIN PERMAINAN & KONTROL ANTARMUKA
   ========================================================================= */
const $ = (id) => document.getElementById(id);

const GameSession = {
  juzObj: null,
  pageNum: 582,
  pageData: null,
  mode: 'ayat',
  level: 1,
  slots: [],
  currentIndex: 0,
  mistakes: 0,
  hintsLeft: 3,
  isBusy: false
};

let activeModalJuzObj = null;
let toastTimer = null;

function showToast(msg, duration = 2800) {
  const el = $('toast');
  if (!el) return;
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), duration);
}

function openModal(id) {
  const el = $(id);
  if (el) el.classList.remove('hidden');
}

function closeModal(id) {
  const el = $(id);
  if (el) el.classList.add('hidden');
}

function showScreen(screenId) {
  document.querySelectorAll('.screen').forEach(sc => sc.classList.remove('active'));
  const target = $(screenId);
  if (target) target.classList.add('active');
  window.scrollTo({ top: 0, behavior: 'instant' });
}

function updateSoundIcons() {
  const icon = AppState.soundOn ? '🔊' : '🔇';
  $('btn-toggle-sound').textContent = icon;
  $('btn-game-sound').textContent = icon;
}

function updateMurottalBadge() {
  const btn = $('btn-toggle-murottal');
  if (!btn) return;
  btn.textContent = AppState.autoMurottal ? '🎙️ Murottal: ON' : '🎙️ Murottal: OFF';
  btn.classList.toggle('murottal-on', AppState.autoMurottal);
  btn.classList.toggle('murottal-off', !AppState.autoMurottal);
}

function updatePageAudioButton() {
  const btn = $('btn-dl-page-audio');
  if (!btn || !GameSession.pageData || !Array.isArray(GameSession.pageData.verses)) return;

  const qariKey = AppState.qari || 'Husary_128kbps';
  const verses = GameSession.pageData.verses;
  const total = verses.length;
  let cached = 0;
  verses.forEach(v => {
    if (AudioDB.has(qariKey, v.surahNumber, v.numberInSurah)) cached++;
  });

  if (total > 0 && cached === total) {
    btn.textContent = '✅ Audio Offline';
    btn.classList.remove('audio-dl-ready');
    btn.classList.add('audio-dl-done');
  } else if (cached > 0) {
    btn.textContent = `⬇️ Audio (${cached}/${total})`;
    btn.classList.add('audio-dl-ready');
    btn.classList.remove('audio-dl-done');
  } else {
    btn.textContent = '⬇️ Audio Hal';
    btn.classList.add('audio-dl-ready');
    btn.classList.remove('audio-dl-done');
  }
}

function updateHeaderUI() {
  $('header-avatar').textContent = AppState.avatar;
  $('header-username').textContent = AppState.username;
  $('header-stars').textContent = getTotalStars();
  $('header-gifts-count').textContent = AppState.unlockedGifts.length;
  updateSoundIcons();
  updateMurottalBadge();

  $('mode-ayat-btn').classList.toggle('active', AppState.mode === 'ayat');
  $('mode-arti-btn').classList.toggle('active', AppState.mode === 'arti');
  $('level-1-btn').classList.toggle('active', AppState.level === 1);
  $('level-2-btn').classList.toggle('active', AppState.level === 2);

  const modeText = AppState.mode === 'ayat' ? 'Susun Ayat' : 'Tebak Arti';
  const levelText = AppState.level === 1 ? 'Level 1 (Bayangan)' : 'Level 2 (Mandiri)';
  $('progress-label').textContent = `Progres: ${modeText} · ${levelText}`;

  const offlineCount = QuranLoader.countOfflinePages();
  const qariKey = AppState.qari || 'Husary_128kbps';
  const qariLabel = MurottalPlayer.getQariCfg(qariKey).label;
  const audioQariCount = AudioDB.countByQari(qariKey);
  const audioTotalCount = AudioDB.countTotal();

  $('btn-open-offline').textContent = `📥 ${offlineCount}/604 · 🎙️ ${audioQariCount}`;
  $('offline-cache-info').textContent =
    `Tersimpan ${offlineCount} dari 604 halaman teks & arti di perangkat ini.`;
  const modeHint = MurottalPlayer.proxyAvailable
    ? 'Mode Server Proxy aktif (siap unduh cepat).'
    : 'Mode langsung aktif.';
  $('offline-audio-info').textContent =
    `Tersimpan ${audioQariCount} ayat audio untuk ${qariLabel} (Total: ${audioTotalCount} ayat). ${modeHint}`;
}

function renderJuzGrid() {
  const grid = $('juz-grid');
  grid.innerHTML = '';

  JUZ_LIST.forEach(juzObj => {
    const totalLevels = juzObj.endPage - juzObj.startPage + 1;
    const doneCount = getJuzCompletedCount(juzObj, AppState.mode, AppState.level);
    const pct = Math.round((doneCount / totalLevels) * 100);

    let hasAnyOffline = false;
    for (let p = juzObj.startPage; p <= juzObj.endPage; p++) {
      if (QuranLoader.isPageOffline(p)) { hasAnyOffline = true; break; }
    }

    const juzAllowed = isJuzAllowedInDemo(juzObj);
    const card = document.createElement('button');
    card.type = 'button';
    card.className = 'juz-card' + (hasAnyOffline && juzAllowed ? ' has-offline' : '') + (!juzAllowed ? ' demo-locked-card' : '');
    card.innerHTML = `
      ${DEMO_CFG.enabled ? (juzAllowed ? '<span class="demo-free-tag">DEMO</span>' : '<span class="demo-lock-tag">🔒</span>') : ''}
      <div class="juz-num">${juzObj.juz}</div>
      <div class="juz-surah">${juzObj.surah}</div>
      <div class="juz-progress-text">${juzAllowed ? `${doneCount}/${totalLevels} level` : 'Versi Full'}</div>
      <div class="juz-mini-bar">
        <div class="juz-mini-fill" style="width:${juzAllowed ? pct : 0}%"></div>
      </div>
    `;

    card.addEventListener('click', () => {
      SoundFX.tap();
      if (!juzAllowed) {
        showDemoLockModal(`Juz ${juzObj.juz} (${juzObj.surah}) terkunci di Mode Demo. Buka Versi Full 30 Juz (604 halaman) sekali bayar tanpa langganan!`);
        return;
      }
      openJuzModal(juzObj);
    });

    grid.appendChild(card);
  });
}

function populateAudioJuzSelect() {
  const sel = $('select-audio-juz');
  if (!sel) return;
  sel.innerHTML = '';
  JUZ_LIST.forEach(j => {
    const opt = document.createElement('option');
    opt.value = j.juz;
    opt.textContent = `Juz ${j.juz} · ${j.surah} (Hal ${j.startPage}-${j.endPage})`;
    if (j.juz === 30) opt.selected = true;
    sel.appendChild(opt);
  });
}

function openJuzModal(juzObj) {
  activeModalJuzObj = juzObj;
  $('modal-juz-title').textContent = `Juz ${juzObj.juz} · ${juzObj.surah}`;
  const modeName = AppState.mode === 'ayat' ? 'Tebak Ayat' : 'Tebak Arti';
  const levelName = AppState.level === 1 ? 'Level 1 (Ada Bayangan)' : 'Level 2 (Tanpa Bayangan)';
  $('modal-juz-desc').textContent =
    `Mode aktif: ${modeName} · ${levelName}. Pilih halaman mushaf di bawah ini (⚡ = teks siap offline).`;

  const qariLabel = MurottalPlayer.getQariCfg(AppState.qari).label;
  $('modal-juz-audio-info').textContent = `🎙️ Audio Juz ${juzObj.juz} (${qariLabel})`;
  $('juz-audio-dl-wrap').classList.add('hidden');

  const list = $('modal-level-list');
  list.innerHTML = '';

  let levelNum = 1;
  for (let p = juzObj.startPage; p <= juzObj.endPage; p++) {
    const stars = getPageStars(p, AppState.mode, AppState.level);
    const isOffline = QuranLoader.isPageOffline(p);
    const subtitle = QuranLoader.getOfflineTitle(p) || `Halaman ${p}`;
    const starStr = stars > 0 ? '⭐'.repeat(stars) + '☆'.repeat(3 - stars) : '☆☆☆';

    const pageAllowed = isPageAllowedInDemo(p);
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'level-item' + (isOffline && pageAllowed ? ' offline-ready' : '') + (!pageAllowed ? ' demo-locked-item' : '');
    item.innerHTML = `
      <div class="level-item-top">
        <span>Level ${levelNum}</span>
        <span>${pageAllowed ? (isOffline ? '⚡' : '☁️') : '🔒'} Hal ${p}</span>
      </div>
      <div class="level-item-name">${subtitle}</div>
      <div class="level-stars">${pageAllowed ? starStr : '<span style="font-size:11px;color:#B45309;font-weight:900;">Buka di Full 30 Juz</span>'}</div>
    `;

    const targetPage = p;
    item.addEventListener('click', () => {
      SoundFX.tap();
      if (!pageAllowed) {
        closeModal('modal-juz');
        showDemoLockModal(`Halaman ${targetPage} terkunci pada Mode Demo. Miliki Versi Full untuk membuka seluruh 604 halaman 30 Juz!`);
        return;
      }
      closeModal('modal-juz');
      startGame(targetPage, juzObj);
    });

    list.appendChild(item);
    levelNum++;
  }

  openModal('modal-juz');
}

function renderGiftModal() {
  checkNewGiftUnlocks();
  const totalStars = getTotalStars();
  $('gift-modal-stars').textContent = totalStars;

  const grid = $('gift-grid');
  grid.innerHTML = '';

  GIFT_CARDS.forEach(card => {
    const unlocked = totalStars >= card.stars;
    const div = document.createElement('div');
    div.className = 'gift-card' + (unlocked ? '' : ' locked');
    div.innerHTML = `
      <div class="gift-emoji">${unlocked ? card.emoji : '🔒'}</div>
      <div class="gift-name">${unlocked ? card.name : 'Kartu Terkunci'}</div>
      <div class="gift-req">${unlocked ? '✅ Terbuka' : `Butuh ${card.stars} ⭐`}</div>
      <div style="font-size:11px; color:#64748B; font-weight:700; margin-top:2px;">
        ${unlocked ? card.desc : `Kumpulkan ${Math.max(0, card.stars - totalStars)} bintang lagi`}
      </div>
    `;
    grid.appendChild(div);
  });
}

function findJuzByPage(pageNum) {
  return JUZ_LIST.find(j => pageNum >= j.startPage && pageNum <= j.endPage) || JUZ_LIST[29];
}

async function startGame(pageNum, juzObj = null) {
  if (DEMO_CFG.enabled && !isPageAllowedInDemo(pageNum)) {
    showDemoLockModal(`Halaman ${pageNum} hanya tersedia di Versi Full 30 Juz. Yuk buka akses penuh sekarang!`);
    return;
  }
  MurottalPlayer.stop();

  const targetJuz = juzObj || findJuzByPage(pageNum);
  GameSession.juzObj = targetJuz;
  GameSession.pageNum = pageNum;
  GameSession.pageData = null;
  GameSession.mode = AppState.mode;
  GameSession.level = AppState.level;
  GameSession.slots = [];
  GameSession.currentIndex = 0;
  GameSession.mistakes = 0;
  GameSession.hintsLeft = 3;
  GameSession.isBusy = false;

  showScreen('screen-game');
  const mushafScroll = $('mushaf-outer');
  if (mushafScroll) mushafScroll.scrollTop = 0;
  updateMurottalBadge();

  $('game-surah-latin').textContent = `Juz ${targetJuz.juz} · hal ${pageNum}`;
  $('game-surah-arabic').textContent = '...';
  $('game-mode-badge').textContent = GameSession.mode === 'ayat' ? '🎯 Tebak Ayat' : '📖 Tebak Arti';
  $('game-level-badge').textContent = GameSession.level === 1 ? '⭐ Level 1' : '⭐⭐ Level 2';
  $('game-accuracy-badge').textContent = '✨ 0 salah';
  $('game-progress-fill').style.width = '0%';
  $('hint-count').textContent = '3';
  $('btn-use-hint').disabled = false;
  $('btn-dl-page-audio').textContent = '⬇️ Audio Hal';
  $('btn-dl-page-audio').disabled = true;

  $('mushaf-surah-banner').textContent = 'جاري التحميل...';
  $('mushaf-basmalah').classList.add('hidden');
  $('mushaf-lines').classList.remove('hidden');
  $('arti-container').classList.add('hidden');
  $('mushaf-lines').innerHTML = `
    <div class="loading-state" dir="ltr">
      <div class="spinner"></div>
      <div>Menyiapkan Halaman ${pageNum}...</div>
    </div>
  `;
  $('word-choices').innerHTML = '';

  try {
    const rawPageData = await QuranLoader.loadPage(pageNum);
    let pageData = rawPageData;
    if (DEMO_CFG.enabled && DEMO_CFG.maxVerses > 0 && rawPageData.verses.length > DEMO_CFG.maxVerses) {
      pageData = Object.assign({}, rawPageData, {
        verses: rawPageData.verses.slice(0, DEMO_CFG.maxVerses),
        isDemoTrimmed: true
      });
    }
    GameSession.pageData = pageData;

    $('game-surah-latin').textContent = `${pageData.surahLatin} · hal ${pageData.page}`;
    $('game-surah-arabic').textContent = pageData.surahShortArabic || pageData.surahArabic;
    $('mushaf-surah-banner').textContent = pageData.surahArabic;
    $('mushaf-basmalah').classList.toggle('hidden', !pageData.showBasmalah);
    $('btn-dl-page-audio').disabled = false;
    updatePageAudioButton();

    if (GameSession.mode === 'ayat') {
      setupAyatMode(pageData);
    } else {
      setupArtiMode(pageData);
    }

    updateHeaderUI();
  } catch (err) {
    $('mushaf-surah-banner').textContent = 'Gagal Memuat';
    $('mushaf-lines').innerHTML = `
      <div class="loading-state" dir="ltr">
        <div style="font-size:38px; margin-bottom:8px;">📡</div>
        <div style="color:#1E293B; margin-bottom:6px;">Halaman ${pageNum} gagal diunduh</div>
        <div style="font-size:12.5px; margin-bottom:14px;">
          Pastikan koneksi internet aktif lalu coba lagi, atau mainkan halaman offline bawaan (Hal 1, 582, 604).
        </div>
        <div style="display:flex; flex-direction:column; gap:8px; max-width:240px; margin:0 auto;">
          <button type="button" id="btn-retry-page" class="btn-blue">🔄 Coba Unduh Lagi</button>
          <button type="button" id="btn-fallback-annaba" class="btn-primary">▶ Main An-Naba' (Offline)</button>
        </div>
      </div>
    `;
    const retryBtn = $('btn-retry-page');
    if (retryBtn) retryBtn.addEventListener('click', () => startGame(pageNum, targetJuz));
    const fbBtn = $('btn-fallback-annaba');
    if (fbBtn) fbBtn.addEventListener('click', () => startGame(582));
  }
}

function setupAyatMode(pageData) {
  const container = $('mushaf-lines');
  container.classList.remove('hidden');
  $('arti-container').classList.add('hidden');
  container.innerHTML = '';

  $('tray-label').textContent = '👆 Ketuk kata berikutnya · Ketuk nomor ayat untuk murottal';
  $('word-choices').classList.add('rtl-choices');

  const hiddenClass = GameSession.level === 1 ? 'level1-hidden' : 'level2-hidden';
  let slotIdx = 0;

  pageData.verses.forEach((verse, vIdx) => {
    const words = tokenizeArabicVerse(verse.arabic);
    words.forEach((word) => {
      const span = document.createElement('span');
      span.className = `mushaf-word ${hiddenClass}`;
      span.dataset.slotIndex = slotIdx;
      span.dataset.verseIndex = vIdx;
      span.textContent = word;
      container.appendChild(span);
      container.appendChild(document.createTextNode(' '));

      GameSession.slots.push({ index: slotIdx, verseIndex: vIdx, text: word, el: span });
      slotIdx++;
    });

    const badge = document.createElement('span');
    badge.className = 'ayah-end-badge';
    badge.dataset.verseIndex = vIdx;
    badge.title = `Putar murottal ayat ${verse.numberInSurah}`;
    badge.textContent = toArabicDigits(verse.numberInSurah);
    badge.addEventListener('click', () => MurottalPlayer.playVerse(vIdx, true));

    container.appendChild(badge);
    container.appendChild(document.createTextNode(' '));
  });

  activateCurrentSlot();
}

function setupArtiMode(pageData) {
  $('mushaf-lines').classList.add('hidden');
  const container = $('arti-container');
  container.classList.remove('hidden');
  container.innerHTML = '';

  $('tray-label').textContent = '👆 Susun arti ayat di bawah ini';
  $('word-choices').classList.remove('rtl-choices');

  const slotClass = GameSession.level === 1 ? 'slot-level1' : 'slot-level2';
  let slotIdx = 0;

  pageData.verses.forEach((verse, vIdx) => {
    const card = document.createElement('div');
    card.className = 'arti-verse-card';
    card.id = `arti-verse-card-${vIdx}`;

    const topRow = document.createElement('div');
    topRow.className = 'arti-top-row';

    const playBtn = document.createElement('button');
    playBtn.type = 'button';
    playBtn.className = 'arti-play-btn';
    playBtn.dataset.verseIndex = vIdx;
    playBtn.textContent = '🔊';
    playBtn.addEventListener('click', () => MurottalPlayer.playVerse(vIdx, true));

    const arDiv = document.createElement('div');
    arDiv.className = 'arti-arabic-text';
    arDiv.innerHTML = `${verse.arabic} <span class="ayah-end-badge" data-verse-index="${vIdx}">${toArabicDigits(verse.numberInSurah)}</span>`;
    const innerBadge = arDiv.querySelector('.ayah-end-badge');
    if (innerBadge) innerBadge.addEventListener('click', () => MurottalPlayer.playVerse(vIdx, true));

    topRow.appendChild(playBtn);
    topRow.appendChild(arDiv);
    card.appendChild(topRow);

    const zone = document.createElement('div');
    zone.className = 'arti-answer-zone';

    const chunks = tokenizeMeaning(verse.meaning);
    chunks.forEach((chunk) => {
      const span = document.createElement('span');
      span.className = `arti-slot ${slotClass}`;
      span.dataset.slotIndex = slotIdx;
      span.dataset.verseIndex = vIdx;
      span.textContent = chunk;
      zone.appendChild(span);

      GameSession.slots.push({ index: slotIdx, verseIndex: vIdx, text: chunk, el: span });
      slotIdx++;
    });

    card.appendChild(zone);
    container.appendChild(card);
  });

  activateCurrentSlot();
}

function activateCurrentSlot() {
  const total = GameSession.slots.length;
  const idx = GameSession.currentIndex;

  const pct = total > 0 ? Math.round((idx / total) * 100) : 100;
  $('game-progress-fill').style.width = `${pct}%`;

  if (idx >= total) {
    finishLevel();
    return;
  }

  const currentSlot = GameSession.slots[idx];

  if (GameSession.mode === 'ayat') {
    currentSlot.el.classList.add('active-slot');
    currentSlot.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  } else {
    document.querySelectorAll('.arti-verse-card').forEach((card, vIdx) => {
      card.classList.toggle('active-verse', vIdx === currentSlot.verseIndex);
      if (vIdx < currentSlot.verseIndex) card.classList.add('done-verse');
    });
    currentSlot.el.classList.add('slot-active');
    const activeCard = $(`arti-verse-card-${currentSlot.verseIndex}`);
    if (activeCard) activeCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  renderChoiceTiles(currentSlot);
}

function renderChoiceTiles(targetSlot) {
  const tray = $('word-choices');
  tray.innerHTML = '';

  const poolTexts = [targetSlot.text];
  const maxChoices = GameSession.mode === 'ayat' ? 7 : 6;

  if (GameSession.mode === 'ayat') {
    for (let i = GameSession.currentIndex + 1; i < GameSession.slots.length && poolTexts.length < maxChoices; i++) {
      const candidate = GameSession.slots[i].text;
      if (!poolTexts.includes(candidate)) poolTexts.push(candidate);
    }
    const earlier = shuffleArray(GameSession.slots.slice(0, GameSession.currentIndex).map(s => s.text));
    for (const candidate of earlier) {
      if (poolTexts.length >= maxChoices) break;
      if (!poolTexts.includes(candidate)) poolTexts.push(candidate);
    }
  } else {
    const sameVerseSlots = GameSession.slots.filter(
      s => s.verseIndex === targetSlot.verseIndex && s.index > targetSlot.index
    );
    for (const s of sameVerseSlots) {
      if (poolTexts.length >= maxChoices) break;
      if (!poolTexts.includes(s.text)) poolTexts.push(s.text);
    }
    const otherSlots = shuffleArray(
      GameSession.slots.filter(s => s.verseIndex !== targetSlot.verseIndex).map(s => s.text)
    );
    for (const candidate of otherSlots) {
      if (poolTexts.length >= maxChoices) break;
      if (!poolTexts.includes(candidate)) poolTexts.push(candidate);
    }
  }

  shuffleArray(poolTexts).forEach(text => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'word-tile ' + (GameSession.mode === 'ayat' ? 'arabic-tile' : 'meaning-tile');
    btn.textContent = text;
    btn.dataset.choiceText = text;
    btn.addEventListener('click', () => handleChoiceTap(btn, text));
    tray.appendChild(btn);
  });
}

function handleChoiceTap(btn, chosenText) {
  if (GameSession.isBusy) return;
  const targetSlot = GameSession.slots[GameSession.currentIndex];
  if (!targetSlot) return;

  if (chosenText === targetSlot.text) {
    btn.classList.add('correct-flash');
    targetSlot.el.classList.remove(
      'level1-hidden', 'level2-hidden', 'active-slot', 'hint-peek',
      'slot-level1', 'slot-level2', 'slot-active'
    );
    targetSlot.el.classList.add(GameSession.mode === 'ayat' ? 'solved' : 'slot-solved');

    const completedVerseIndex = targetSlot.verseIndex;
    GameSession.currentIndex++;

    const nextSlot = GameSession.slots[GameSession.currentIndex];
    const verseJustFinished = !nextSlot || nextSlot.verseIndex !== completedVerseIndex;

    if (verseJustFinished) {
      SoundFX.verseComplete();
      if (AppState.autoMurottal && AppState.soundOn) {
        MurottalPlayer.playVerse(completedVerseIndex, false);
      }
    } else {
      SoundFX.correct();
    }

    activateCurrentSlot();
  } else {
    GameSession.isBusy = true;
    GameSession.mistakes++;
    $('game-accuracy-badge').textContent = `✨ ${GameSession.mistakes} salah`;
    SoundFX.wrong();
    btn.classList.add('wrong-shake');
    setTimeout(() => {
      btn.classList.remove('wrong-shake');
      GameSession.isBusy = false;
    }, 360);
  }
}

function useHint() {
  if (GameSession.hintsLeft <= 0) {
    showToast('Bantuan lampu untuk level ini sudah habis.');
    return;
  }

  const targetSlot = GameSession.slots[GameSession.currentIndex];
  if (!targetSlot) return;

  GameSession.hintsLeft--;
  $('hint-count').textContent = GameSession.hintsLeft;
  if (GameSession.hintsLeft <= 0) $('btn-use-hint').disabled = true;

  SoundFX.hint();
  targetSlot.el.classList.add('hint-peek');

  document.querySelectorAll('#word-choices .word-tile').forEach(t => {
    if (t.dataset.choiceText === targetSlot.text) {
      t.style.borderColor = '#F59E0B';
      t.style.background = '#FEF3C7';
      t.style.transform = 'scale(1.06)';
      setTimeout(() => { t.style.transform = ''; }, 500);
    }
  });
}

function finishLevel() {
  if (!AppState.autoMurottal) SoundFX.win();

  document.querySelectorAll('.arti-verse-card').forEach(c => {
    c.classList.remove('active-verse');
    c.classList.add('done-verse');
  });

  let earnedStars = 3;
  if (GameSession.mistakes > 6) earnedStars = 1;
  else if (GameSession.mistakes > 2) earnedStars = 2;

  const key = getProgressKey(GameSession.pageNum, GameSession.mode, GameSession.level);
  const prevBest = AppState.progress[key] || 0;
  if (earnedStars > prevBest) {
    AppState.progress[key] = earnedStars;
    saveState();
  }

  const newlyUnlocked = checkNewGiftUnlocks();
  updateHeaderUI();
  renderJuzGrid();

  $('win-stars').textContent = '⭐'.repeat(earnedStars) + '☆'.repeat(3 - earnedStars);
  $('win-heading').textContent =
    earnedStars === 3 ? 'MasyaAllah, Sempurna!' :
    earnedStars === 2 ? 'Hebat Sekali!' : 'Alhamdulillah, Selesai!';

  const surahLabel = GameSession.pageData ? GameSession.pageData.surahLatin : `Halaman ${GameSession.pageNum}`;
  $('win-sub').textContent =
    `Halaman ${GameSession.pageNum} (${surahLabel}) selesai dengan ${GameSession.mistakes} kali salah.`;

  const rewardBanner = $('win-reward-banner');
  if (newlyUnlocked.length > 0) {
    const firstCard = newlyUnlocked[0];
    $('win-reward-content').textContent = `${firstCard.emoji} ${firstCard.name}`;
    rewardBanner.classList.remove('hidden');
  } else {
    rewardBanner.classList.add('hidden');
  }

  const nextBtn = $('btn-win-next');
  if (DEMO_CFG.enabled && GameSession.pageData && GameSession.pageData.isDemoTrimmed) {
    $('win-sub').textContent =
      `MasyaAllah! Kamu menuntaskan ${DEMO_CFG.maxVerses} ayat demo dari ${surahLabel}. Buka Versi Full untuk memainkan seluruh ayat 30 Juz!`;
    nextBtn.classList.remove('hidden');
    nextBtn.textContent = '🔓 Buka Versi Full 30 Juz';
  } else if (DEMO_CFG.enabled && !isPageAllowedInDemo(GameSession.pageNum + 1)) {
    nextBtn.classList.remove('hidden');
    nextBtn.textContent = '🔓 Buka Versi Full 30 Juz';
  } else if (GameSession.pageNum < 604) {
    nextBtn.classList.remove('hidden');
    nextBtn.textContent = `▶ Lanjut Halaman ${GameSession.pageNum + 1}`;
  } else {
    nextBtn.classList.add('hidden');
  }

  setTimeout(() => openModal('modal-win'), 420);
}

/* =========================================================================
   10. REGISTRASI PWA (SERVICE WORKER & INSTALL LAYAR UTAMA HP)
   ========================================================================= */
const PWAInstaller = {
  deferredPrompt: null,

  init() {
    if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('./sw.js').catch((err) => {
          console.warn('Service Worker gagal didaftarkan:', err);
        });
      });
    }

    const banner = $('pwa-install-banner');
    const desc = $('pwa-install-desc');
    const btn = $('btn-install-pwa');

    const isStandalone =
      window.matchMedia('(display-mode: standalone)').matches ||
      window.navigator.standalone === true;
    if (isStandalone) return;

    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      this.deferredPrompt = e;
      if (banner) banner.classList.remove('hidden');
    });

    window.addEventListener('appinstalled', () => {
      this.deferredPrompt = null;
      if (banner) banner.classList.add('hidden');
      showToast('Al-Qur\'an Go berhasil dipasang di layar utama! 🎉', 3200);
    });

    const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
    if (isIOS && banner && desc) {
      desc.textContent = 'Ketuk tombol Bagikan (Share) lalu pilih "Tambah ke Layar Utama".';
      banner.classList.remove('hidden');
    }

    if (btn) {
      btn.addEventListener('click', async () => {
        SoundFX.tap();
        if (this.deferredPrompt) {
          this.deferredPrompt.prompt();
          const { outcome } = await this.deferredPrompt.userChoice;
          if (outcome === 'accepted') banner.classList.add('hidden');
          this.deferredPrompt = null;
        } else if (isIOS) {
          showToast('Di Safari: ketuk ikon Bagikan (⬆️) → "Tambah ke Layar Utama"', 4200);
        } else {
          showToast('Buka menu browser (⋮) lalu pilih "Tambahkan ke layar utama" / "Install app".', 3600);
        }
      });
    }
  }
};

/* =========================================================================
   11. KONTROL UNDUHAN TEKS & AUDIO OFFLINE + EVENT LISTENER
   ========================================================================= */
function setProgressBar(wrapId, textId, fillId, show, pct = 0, text = '') {
  const wrap = $(wrapId);
  const txt = $(textId);
  const fill = $(fillId);
  if (!wrap) return;
  wrap.classList.toggle('hidden', !show);
  if (txt) txt.textContent = text;
  if (fill) fill.style.width = `${Math.min(100, Math.max(0, pct))}%`;
}

async function runDownloadJuzAudio(juzNumber, uiTargets) {
  const { wrapId, textId, fillId, disableBtns } = uiTargets;
  disableBtns.forEach(b => { if (b) b.disabled = true; });
  setProgressBar(wrapId, textId, fillId, true, 3, `Menyiapkan daftar ayat Juz ${juzNumber}...`);

  try {
    // 1. Pastikan halaman teks Juz tersebut tersedia agar tahu daftar ayatnya
    const juzRes = await QuranLoader.downloadJuzOffline(juzNumber, (done, total) => {
      const pct = Math.round((done / total) * 20);
      setProgressBar(wrapId, textId, fillId, true, pct, `Menyiapkan halaman Juz ${juzNumber} (${done}/${total})...`);
    });

    const allVerses = [];
    juzRes.pages.forEach(p => {
      if (p && Array.isArray(p.verses)) p.verses.forEach(v => allVerses.push(v));
    });

    if (allVerses.length === 0) {
      throw new Error('Tidak dapat mengambil daftar ayat. Cek koneksi internet.');
    }

    // 2. Unduh seluruh audio MP3 ke IndexedDB
    const res = await MurottalPlayer.downloadVersesBatch(allVerses, (done, total, ok) => {
      const pct = 20 + Math.round((done / total) * 80);
      setProgressBar(wrapId, textId, fillId, true, pct, `Mengunduh audio Juz ${juzNumber}: ${ok}/${total} ayat...`);
    });

    updateHeaderUI();
    renderJuzGrid();
    updatePageAudioButton();

    if (res.aborted) {
      setProgressBar(wrapId, textId, fillId, true, Math.round((res.ok / res.total) * 100), `Dihentikan (${res.ok}/${res.total} ayat tersimpan).`);
      showToast(`Unduhan dihentikan. ${res.ok} ayat sudah tersimpan offline.`);
    } else if (res.ok === res.total) {
      SoundFX.win();
      setProgressBar(wrapId, textId, fillId, true, 100, `Selesai! ${res.ok} ayat audio Juz ${juzNumber} siap offline ⚡`);
      showToast(`Audio Juz ${juzNumber} lengkap (${res.ok} ayat) siap diputar offline! 🎙️`, 3400);
    } else {
      setProgressBar(wrapId, textId, fillId, true, Math.round((res.ok / res.total) * 100), `Tersimpan ${res.ok}/${res.total} ayat. Coba lagi untuk melengkapi.`);
      showToast(`Tersimpan ${res.ok} dari ${res.total} ayat audio Juz ${juzNumber}.`);
    }
  } catch (err) {
    setProgressBar(wrapId, textId, fillId, true, 0, 'Gagal mengunduh audio. Periksa koneksi internet.');
    showToast('Gagal mengunduh audio. Pastikan internet aktif.');
  } finally {
    disableBtns.forEach(b => { if (b) b.disabled = false; });
  }
}

function bindEvents() {
  document.querySelectorAll('[data-mode]').forEach(btn => {
    btn.addEventListener('click', () => {
      SoundFX.tap();
      AppState.mode = btn.dataset.mode;
      saveState();
      updateHeaderUI();
      renderJuzGrid();
    });
  });

  document.querySelectorAll('[data-level]').forEach(btn => {
    btn.addEventListener('click', () => {
      SoundFX.tap();
      AppState.level = Number(btn.dataset.level);
      saveState();
      updateHeaderUI();
      renderJuzGrid();
    });
  });

  const toggleSoundHandler = () => {
    AppState.soundOn = !AppState.soundOn;
    if (!AppState.soundOn) MurottalPlayer.stop();
    saveState();
    updateSoundIcons();
    if (AppState.soundOn) SoundFX.tap();
    showToast(AppState.soundOn ? 'Suara diaktifkan 🔊' : 'Suara dimatikan 🔇');
  };
  $('btn-toggle-sound').addEventListener('click', toggleSoundHandler);
  $('btn-game-sound').addEventListener('click', toggleSoundHandler);

  $('btn-toggle-murottal').addEventListener('click', () => {
    SoundFX.tap();
    AppState.autoMurottal = !AppState.autoMurottal;
    if (!AppState.autoMurottal) MurottalPlayer.stop();
    saveState();
    updateMurottalBadge();
    showToast(AppState.autoMurottal ? 'Murottal otomatis per ayat aktif 🎙️' : 'Murottal otomatis dimatikan');
  });

  // Tombol Unduh Audio Khusus Halaman yang Sedang Dibuka
  $('btn-dl-page-audio').addEventListener('click', async () => {
    if (!GameSession.pageData || !Array.isArray(GameSession.pageData.verses)) return;
    const btn = $('btn-dl-page-audio');
    btn.disabled = true;
    SoundFX.tap();
    btn.textContent = '⏳ 0%...';

    const res = await MurottalPlayer.downloadVersesBatch(GameSession.pageData.verses, (done, total) => {
      btn.textContent = `⏳ ${done}/${total}`;
    });

    btn.disabled = false;
    updatePageAudioButton();
    updateHeaderUI();

    if (res.ok === res.total && res.total > 0) {
      SoundFX.correct();
      showToast(`Audio halaman ${GameSession.pageNum} (${res.ok} ayat) siap diputar offline! ⚡`, 3000);
    } else {
      showToast(`Tersimpan ${res.ok}/${res.total} audio ayat. Pastikan internet aktif.`);
    }
  });

  // Tombol Unduh Audio di Modal Pilih Halaman per Juz
  $('btn-dl-modal-juz-audio').addEventListener('click', () => {
    if (DEMO_CFG.enabled) {
      closeModal('modal-juz');
      showDemoLockModal('Unduh Audio Murottal Offline per Juz hanya tersedia di Versi Full 30 Juz.');
      return;
    }
    if (!activeModalJuzObj) return;
    SoundFX.tap();
    runDownloadJuzAudio(activeModalJuzObj.juz, {
      wrapId: 'juz-audio-dl-wrap',
      textId: 'juz-audio-dl-text',
      fillId: 'juz-audio-dl-fill',
      disableBtns: [$('btn-dl-modal-juz-audio')]
    });
  });

  $('btn-cancel-juz-audio').addEventListener('click', () => {
    SoundFX.tap();
    MurottalPlayer.cancelDownload();
  });

  $('btn-cancel-audio-dl').addEventListener('click', () => {
    SoundFX.tap();
    MurottalPlayer.cancelDownload();
  });

  $('btn-murottal-replay').addEventListener('click', () => MurottalPlayer.replayLast());
  $('btn-murottal-stop').addEventListener('click', () => MurottalPlayer.stop());

  $('btn-game-back').addEventListener('click', () => {
    SoundFX.tap();
    MurottalPlayer.stop();
    showScreen('screen-home');
    updateHeaderUI();
    renderJuzGrid();
  });

  $('btn-use-hint').addEventListener('click', useHint);

  $('btn-open-gifts').addEventListener('click', () => {
    SoundFX.tap();
    renderGiftModal();
    openModal('modal-gifts');
  });

  const openProfileModal = () => {
    SoundFX.tap();
    $('input-username').value = AppState.username;
    $('chk-auto-murottal').checked = !!AppState.autoMurottal;
    $('select-qari').value = AppState.qari || 'Husary_128kbps';
    document.querySelectorAll('.avatar-opt').forEach(opt => {
      opt.classList.toggle('selected', opt.dataset.avatar === AppState.avatar);
    });
    updateHeaderUI();
    openModal('modal-profile');
  };
  $('btn-open-profile').addEventListener('click', openProfileModal);
  $('btn-open-offline').addEventListener('click', openProfileModal);

  document.querySelectorAll('.avatar-opt').forEach(opt => {
    opt.addEventListener('click', () => {
      SoundFX.tap();
      document.querySelectorAll('.avatar-opt').forEach(o => o.classList.remove('selected'));
      opt.classList.add('selected');
    });
  });

  $('select-qari').addEventListener('change', () => {
    AppState.qari = $('select-qari').value;
    saveState();
    updateHeaderUI();
    updatePageAudioButton();
  });

  $('btn-save-profile').addEventListener('click', () => {
    SoundFX.correct();
    const nameVal = $('input-username').value.trim();
    const selectedAvatar = document.querySelector('.avatar-opt.selected');
    AppState.username = nameVal || 'Sahabat Quran';
    if (selectedAvatar) AppState.avatar = selectedAvatar.dataset.avatar;
    AppState.autoMurottal = $('chk-auto-murottal').checked;
    AppState.qari = $('select-qari').value;
    saveState();
    updateHeaderUI();
    updatePageAudioButton();
    closeModal('modal-profile');
    showToast(`Pengaturan ${AppState.username} tersimpan!`);
  });

  // Unduh Teks Lengkap 30 Juz
  $('btn-cache-all30').addEventListener('click', async () => {
    if (DEMO_CFG.enabled) {
      closeModal('modal-profile');
      showDemoLockModal('Fitur Unduh Lengkap 30 Juz Offline hanya tersedia di Versi Full.');
      return;
    }
    const btnAll = $('btn-cache-all30');
    const btnJ30 = $('btn-cache-juz30');
    btnAll.disabled = true;
    btnJ30.disabled = true;
    SoundFX.tap();
    setProgressBar('dl-progress-wrap', 'dl-progress-text', 'dl-progress-fill', true, 5, 'Menghubungi server Al-Qur\'an...');

    try {
      const count = await QuranLoader.downloadAll30Juz((pct, msg) => {
        setProgressBar('dl-progress-wrap', 'dl-progress-text', 'dl-progress-fill', true, pct, msg);
      });
      SoundFX.win();
      updateHeaderUI();
      renderJuzGrid();
      showToast(`Alhamdulillah! ${count} halaman teks 30 Juz siap offline! ⚡`, 3600);
    } catch (err) {
      setProgressBar('dl-progress-wrap', 'dl-progress-text', 'dl-progress-fill', true, 0, 'Unduhan gagal. Pastikan internet stabil lalu coba lagi.');
      showToast('Gagal mengunduh teks 30 Juz. Cek jaringan internetmu.', 3400);
    } finally {
      btnAll.disabled = false;
      btnJ30.disabled = false;
    }
  });

  // Unduh Teks Juz 30 Saja
  $('btn-cache-juz30').addEventListener('click', async () => {
    const btnAll = $('btn-cache-all30');
    const btnJ30 = $('btn-cache-juz30');
    btnAll.disabled = true;
    btnJ30.disabled = true;
    SoundFX.tap();
    setProgressBar('dl-progress-wrap', 'dl-progress-text', 'dl-progress-fill', true, 4, 'Mengunduh teks Juz 30 (0/23)...');

    const result = await QuranLoader.downloadJuzOffline(30, (done, total) => {
      const pct = Math.round((done / total) * 100);
      setProgressBar('dl-progress-wrap', 'dl-progress-text', 'dl-progress-fill', true, pct, `Mengunduh teks Juz 30 (${done}/${total} hal)...`);
    });

    btnAll.disabled = false;
    btnJ30.disabled = false;
    updateHeaderUI();
    renderJuzGrid();

    if (result.ok === result.total) {
      SoundFX.win();
      setProgressBar('dl-progress-wrap', 'dl-progress-text', 'dl-progress-fill', true, 100, 'Teks Juz 30 lengkap (23 hal) tersimpan offline! ⚡');
      showToast('Teks Juz 30 lengkap siap dimainkan offline! ⚡', 3200);
    } else {
      showToast(`Tersimpan ${result.ok} dari ${result.total} halaman. Cek internetmu.`, 3200);
    }
  });

  // Unduh Audio Surah Utama (Hal 1, 582, 604 -> 38 Ayat)
  $('btn-dl-starter-audio').addEventListener('click', async () => {
    const btnStarter = $('btn-dl-starter-audio');
    const btnJuz = $('btn-dl-juz-audio');
    btnStarter.disabled = true;
    btnJuz.disabled = true;
    SoundFX.tap();

    const starterVerses = [
      ...BUILTIN_PAGES[1].verses,
      ...BUILTIN_PAGES[582].verses,
      ...BUILTIN_PAGES[604].verses
    ];

    setProgressBar('audio-dl-wrap', 'audio-dl-text', 'audio-dl-fill', true, 4, `Mengunduh audio surah utama (0/${starterVerses.length})...`);

    const res = await MurottalPlayer.downloadVersesBatch(starterVerses, (done, total, ok) => {
      const pct = Math.round((done / total) * 100);
      setProgressBar('audio-dl-wrap', 'audio-dl-text', 'audio-dl-fill', true, pct, `Mengunduh audio utama: ${ok}/${total} ayat...`);
    });

    btnStarter.disabled = false;
    btnJuz.disabled = false;
    updateHeaderUI();
    updatePageAudioButton();

    if (res.aborted) {
      showToast(`Unduhan dihentikan (${res.ok} ayat tersimpan).`);
    } else if (res.ok === res.total) {
      SoundFX.win();
      setProgressBar('audio-dl-wrap', 'audio-dl-text', 'audio-dl-fill', true, 100, `Selesai! ${res.ok} ayat audio utama siap offline ⚡`);
      showToast('Audio Al-Fatihah, An-Naba\', & 3 Qul siap diputar offline! 🎙️', 3400);
    } else {
      showToast(`Tersimpan ${res.ok} dari ${res.total} ayat audio. Cek koneksi internet.`);
    }
  });

  // Unduh Audio Juz Terpilih di Modal Profil
  $('btn-dl-juz-audio').addEventListener('click', () => {
    if (DEMO_CFG.enabled) {
      closeModal('modal-profile');
      showDemoLockModal('Unduh Audio Murottal Offline per Juz hanya tersedia di Versi Full 30 Juz.');
      return;
    }
    const juzNum = Number($('select-audio-juz').value) || 30;
    SoundFX.tap();
    runDownloadJuzAudio(juzNum, {
      wrapId: 'audio-dl-wrap',
      textId: 'audio-dl-text',
      fillId: 'audio-dl-fill',
      disableBtns: [$('btn-dl-juz-audio'), $('btn-dl-starter-audio')]
    });
  });

  // Hapus Cache Audio Murottal
  $('btn-clear-audio').addEventListener('click', async () => {
    if (confirm('Hapus semua file audio murottal offline di perangkat ini? (Bintang & teks 30 Juz tidak terhapus)')) {
      MurottalPlayer.stop();
      await AudioDB.clearAll();
      updateHeaderUI();
      updatePageAudioButton();
      showToast('Cache audio murottal berhasil dibersihkan.');
    }
  });

  // Reset Progres
  $('btn-reset-progress').addEventListener('click', () => {
    if (confirm('Yakin ingin menghapus semua bintang dan kartu hadiah yang sudah terkumpul?')) {
      AppState.progress = {};
      AppState.unlockedGifts = [];
      saveState();
      updateHeaderUI();
      renderJuzGrid();
      closeModal('modal-profile');
      showToast('Progres berhasil direset.');
    }
  });

  $('btn-win-next').addEventListener('click', () => {
    SoundFX.tap();
    closeModal('modal-win');
    if (DEMO_CFG.enabled && ((GameSession.pageData && GameSession.pageData.isDemoTrimmed) || !isPageAllowedInDemo(GameSession.pageNum + 1))) {
      window.location.href = DEMO_CFG.buyUrl || './checkout.php';
      return;
    }
    startGame(GameSession.pageNum + 1);
  });

  $('btn-win-replay').addEventListener('click', () => {
    SoundFX.tap();
    closeModal('modal-win');
    startGame(GameSession.pageNum, GameSession.juzObj);
  });

  $('btn-win-home').addEventListener('click', () => {
    SoundFX.tap();
    MurottalPlayer.stop();
    closeModal('modal-win');
    showScreen('screen-home');
    updateHeaderUI();
    renderJuzGrid();
  });

  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      SoundFX.tap();
      closeModal(btn.dataset.closeModal);
    });
  });

  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) closeModal(backdrop.id);
    });
  });
}

window.startGame = startGame;

document.addEventListener('DOMContentLoaded', async () => {
  MurottalPlayer.init();
  PWAInstaller.init();
  populateAudioJuzSelect();
  bindEvents();
  await Promise.all([QuranLoader.init(), AudioDB.init(), MurottalPlayer.checkProxy()]);
  checkNewGiftUnlocks();
  updateHeaderUI();
  renderJuzGrid();
});
