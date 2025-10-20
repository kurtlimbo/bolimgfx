<?php include 'db_connect.php'; ?>

<!doctype html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BOLIM GFX</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root{ --bg:#252525; --panel:#121214; --ring:rgba(255,255,255,.08); --accent:#f0f; }
    body{ background:var(--bg); color:#fff; scroll-behavior:smooth; }

    /* GRID (no gaps) */
    .grid-auto{ display:grid; gap:0; grid-template-columns: repeat(auto-fill, minmax(min(100%,260px), 1fr)); }

    /* TILES */
    .tile{ position:relative; overflow:hidden; border-radius:0; box-shadow:none; transform:translateY(8px);
           opacity:0; animation:fadeUp .6s cubic-bezier(.21,1,.21,1) forwards; cursor:zoom-in; }
    .tile img{ aspect-ratio:1/1; width:100%; object-fit:cover; display:block;
               transition:transform .35s ease, filter .35s ease;
               user-select:none; -webkit-user-drag:none; -webkit-touch-callout:none; image-rendering:auto; }
    .tile:hover img{ transform:scale(1.03); filter:grayscale(100%); }

    /* subtle dark overlay on hover */
    .tile::after{ content:""; position:absolute; inset:0;
      background:linear-gradient(to top,rgba(0,0,0,.45),transparent 50%);
      opacity:0; transition:opacity .35s ease; }
    .tile:hover::after{ opacity:1; }

    /* guard layer to catch right-click / long-press / drag */
    .img-guard{ position:absolute; inset:0; pointer-events:auto; }

    .tile .caption{ pointer-events:none; position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
      opacity:0; transition:opacity .25s ease; }
    .tile:hover .caption{ opacity:1; }
    .caption .pill{ background:rgba(0,0,0,.65); backdrop-filter:saturate(130%) blur(2px);
      color:#fff; padding:.75rem 1rem; border-radius:.25rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }

    /* intro */
    .fade-out { opacity:0; transition:opacity .8s ease; }

    /* header + buttons */
    .nav-link{ transition:color .2s ease, transform .2s ease }
    .nav-link:hover{ color:#FA02AF; transform:translateY(-1px) }
    .icon-btn{ transition: transform .2s ease, background-color .2s ease }
    .icon-btn:hover{ transform:translateY(-1px); background:rgba(255,255,255,.08) }
    .btn{ border-radius:9999px; background:#fff; color:#000; padding:.75rem 1.25rem; font-weight:700; display:inline-flex; align-items:center; gap:.5rem; box-shadow:0 8px 30px rgba(255,255,255,.08); transition: transform .15s ease, box-shadow .2s ease, opacity .2s ease; }
    .btn:hover{ box-shadow:0 12px 40px rgba(255,0,200,.18) } .btn:active{ transform:translateY(1px) }

    /* lightbox (modal) */
    .lightbox{ position:fixed; inset:0; z-index:70; background:rgba(0,0,0,.86); display:none; align-items:center; justify-content:center; padding:2rem; }
    .lightbox.open{ display:flex; }
    .lightbox-inner{ max-width:92vw; max-height:90vh; display:flex; flex-direction:column; gap:.75rem; align-items:center; }
    .lightbox-img-wrap{ position:relative; }
    .lightbox-img{ max-width:92vw; max-height:80vh; object-fit:contain; border-radius:.5rem; box-shadow:0 20px 60px rgba(0,0,0,.6);
      user-select:none; -webkit-user-drag:none; -webkit-touch-callout:none; image-rendering:auto; }
    .lb-guard{ position:absolute; inset:0; } /* guard inside modal */
    .lightbox-caption{ font-weight:700; letter-spacing:.04em; text-transform:uppercase; background:rgba(0,0,0,.45); padding:.5rem .75rem; border-radius:.375rem; }

    .lb-btn{ position:absolute; top:50%; transform:translateY(-50%);
      background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
      width:42px; height:42px; display:grid; place-items:center; border-radius:.5rem; cursor:pointer; }
    .lb-btn:hover{ background:rgba(255,255,255,.2) }
    .lb-prev{ left:1rem } .lb-next{ right:1rem }
    .lb-close{ position:absolute; top:1rem; right:1rem; width:40px; height:40px; border-radius:.5rem;
      background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); display:grid; place-items:center; cursor:pointer; }
    .lb-close:hover{ background:rgba(255,255,255,.2) }

    @keyframes fadeUp{ to{ opacity:1; transform:translateY(0) } }

    /* CONTACT OVERLAY (appears on click) */
#contactOverlay{
  position:fixed; inset:0; z-index:60;
  display:flex; align-items:center; justify-content:center;
  background: radial-gradient(1200px 600px at 50% -10%, rgba(255,0,200,.08), transparent 65%), rgba(0,0,0,.65);
  backdrop-filter: blur(10px);
  opacity:0; pointer-events:none; transition:opacity .35s ease;
}
#contactOverlay.show{ opacity:1; pointer-events:auto; }

.overlay-card{
  width:min(92vw,780px);
  background:rgba(20,20,20,.92);
  border:1px solid rgba(255,255,255,.08);
  border-radius:16px;
  box-shadow:0 30px 120px rgba(0,0,0,.6);
}

/* prevent page scroll while overlay is open */
body.no-scroll{ overflow:hidden; }

/* close button */
.contact-close{
  position:absolute; top:.75rem; right:.75rem;
  width:36px; height:36px; border-radius:9999px;
  display:grid; place-items:center;
  border:1px solid rgba(255,255,255,.12);
  background:rgba(255,255,255,.04);
  transition:background .2s ease, transform .12s ease;
}
.contact-close:hover{ background:rgba(255,255,255,.08) }
.contact-close:active{ transform:translateY(1px) }

/* SOCIAL OVERLAY (same vibe as contact) */
#socialOverlay{
  position:fixed; inset:0; z-index:60;
  display:flex; align-items:center; justify-content:center;
  background: radial-gradient(1200px 600px at 50% -10%, rgba(255,0,200,.08), transparent 65%), rgba(0,0,0,.65);
  backdrop-filter: blur(10px);
  opacity:0; pointer-events:none; transition:opacity .35s ease;
}
#socialOverlay.show{ opacity:1; pointer-events:auto; }

.social-card{
  width:min(92vw,520px);
  background:rgba(20,20,20,.92);
  border:1px solid rgba(255,255,255,.08);
  border-radius:16px;
  box-shadow:0 30px 120px rgba(0,0,0,.6);
}

.social-close{
  position:absolute; top:.75rem; right:.75rem;
  width:36px; height:36px; border-radius:9999px;
  display:grid; place-items:center;
  border:1px solid rgba(255,255,255,.12);
  background:rgba(255,255,255,.04);
  transition:background .2s ease, transform .12s ease;
}
.social-close:hover{ background:rgba(255,255,255,.08) }
.social-close:active{ transform:translateY(1px) }

/* disable scroll kapag bukas modal */
body.no-scroll{ overflow:hidden; }

/* Float back-to-top button — magenta like the logo */
#toTopBtn{
  background: #FA02AF !important;
  position: fixed; right: 18px; bottom: 22px; z-index: 65;
  width: 55px; height: 55px; border-radius: 9999px;
  display: grid; place-items: center; cursor: pointer;

  /* kulay + ring + shadow */
  background: var(--accent);
  color:#fff;
  border: 1px solid rgba(255,255,255,.15);
  box-shadow:
    inset 0 0 0 2px rgba(255,255,255,.08),
    0 14px 36px rgba(255, 0, 168, .35);

  transform: translateY(16px); opacity: 0; pointer-events: none;
  transition: opacity .25s ease, transform .25s ease, filter .2s ease, box-shadow .2s ease;
}
#toTopBtn:hover{
  filter: brightness(1.05);
  box-shadow:
    inset 0 0 0 2px rgba(255,255,255,.12),
    0 18px 46px rgba(255, 0, 168, .45);
}
#toTopBtn:active{ transform: translateY(1px); }
#toTopBtn.show{ opacity: 1; transform: translateY(0); pointer-events: auto; }


  </style>
</head>
<body class="min-h-screen">

<!-- WELCOME -->
<section id="welcome" class="min-h-screen flex flex-col items-center justify-center text-center px-4 bg-gradient-to-b from-[#1a1a1a] to-[#252525]">
  <div class="max-w-xl">
    <div class="mx-auto w-20 h-20 rounded-full ring-2 ring-white/20 overflow-hidden mb-5 shadow-lg select-none">
      <img src="assets/images/bolim.jpg" alt="Kurt Logo" class="w-full h-full object-cover" />
    </div>
    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight select-none">Welcome to <span style="color:#FA02AF;" class="text-fuchsia-300 select-none">Kurt’s</span> Portfolio</h1>
    <p class="mt-3 text-white/70 select-none">Scroll down or click the button below to view my artworks.</p>
    <div class="mt-6"><button id="scrollBtn" class="btn bg-white text-black">View My Artworks ↓</button></div>
  </div>
</section>

<!-- HEADER -->
<header id="siteHeader" class="sticky top-0 z-50 backdrop-blur-md bg-white/5 relative">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10 lg:py-12 flex items-center justify-between">
    <a class="flex items-center gap-3 pointer-events-auto" href="#">
      <span class="w-12 h-12 md:w-14 md:h-14 rounded-full ring-2 ring-white/10 shadow-sm overflow-hidden bg-[#ff00a8]">
        <img src="assets/images/bolim.jpg" alt="Bolim GFX logo" class="w-full h-full object-cover select-none" />
      </span>
      <div class="leading-none">
        <div class="text-[10px] opacity-80 uppercase tracking-wider">Kurt Alejandro</div>
        <div class="font-extrabold tracking-widest text-lg md:text-xl">BOLIM GFX</div>
      </div>
    </a>

    <div class="hidden md:flex items-center gap-10">
      <nav class="flex gap-8 text-sm font-semibold">
        <a href="#artworks" class="nav-link">ARTWORKS</a>
        <a href="#contact"  class="nav-link">CONTACT</a>
        <a href="#"         class="nav-link" data-open-social>SOCIAL LINKS</a>
      </nav>
    </div>

    <button id="menuBtn" type="button"
      class="md:hidden p-3 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/20"
      aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
  </div>

  <div id="mobileMenu" class="md:hidden hidden absolute z-50 top-full inset-x-0 bg-[#111]/90 backdrop-blur">
    <nav class="px-4 py-4 flex flex-col gap-2 text-sm font-semibold">
      <a href="#artworks" class="py-3 px-2 rounded hover:bg-white/10">ARTWORKS</a>
      <a href="#contact"  class="py-3 px-2 rounded hover:bg-white/10">CONTACT</a>
      <a href="#" class="py-3 px-2 rounded hover:bg-white/10" data-open-social>SOCIAL LINKS</a>
    </nav>
  </div>
</header>

<!-- ARTWORKS -->
<main id="artworks" class="max-w-[1400px] mx-auto mt-0">
  <div class="grid-auto">
    <?php
      // auto-scan: assets/artworks/*
      $folder = __DIR__ . '/assets/artworks';
      $base   = 'assets/artworks';
      $files  = glob($folder.'/*.{jpg,jpeg,png,webp,gif,JPG,JPEG,PNG,WEBP,GIF}', GLOB_BRACE);
      usort($files, fn($a,$b)=>filemtime($b)<=>filemtime($a));

      if (!$files) {
        echo '<div class="col-span-full text-center py-16 text-white/60">Walang images sa <code>assets/artworks/</code>.</div>';
      }

      foreach ($files as $i => $fullpath) {
        $filename = basename($fullpath);
        $src   = $base . '/' . $filename; // original path (we won't put this as <img src>)
        $title = ucwords(str_replace(['-','_'],' ', pathinfo($filename, PATHINFO_FILENAME)));
        $srcEsc = htmlspecialchars($src, ENT_QUOTES);
        $titleEsc = htmlspecialchars($title, ENT_QUOTES);

        // NOTE: we leave src as a tiny blank pixel and feed the real path via data attributes.
        echo <<<HTML
        <figure class="tile" data-index="$i" data-full="$srcEsc" data-title="$titleEsc" tabindex="0" aria-label="$titleEsc">
          <img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="$titleEsc" loading="lazy" decoding="async">
          <div class="img-guard"></div>
          <figcaption class="caption"><span class="pill text-sm md:text-base">$titleEsc</span></figcaption>
        </figure>
HTML;
      }
    ?>
  </div>
</main>

<!-- FOOTER -->
<footer id="contact" class="mt-0 border-t border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid gap-6 md:grid-cols-2 items-center">
    <div>
      <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight select-none">Let's work together.</h2>
      <p class="mt-2 text-white/70 select-none">For commissions and collabs, send me a message.</p>
    </div>
    <div class="md:text-right">
    <a href="#" class="btn bg-white text-black" data-open-contact>Contact Me →</a>
    </div>
  </div>
</footer>

<!-- CONTACT OVERLAY (hidden by default, opens on click) -->
<div id="contactOverlay" aria-hidden="true">
  <div class="overlay-card relative mx-4">
    <button type="button" class="contact-close" aria-label="Close" data-close-contact>
      <!-- simple X icon -->
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M6 6l12 12M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </button>

    <div class="px-6 sm:px-8 pt-8 pb-6">
      <div class="text-center">
        <div class="text-[6px] tracking-[0.4em] uppercase opacity-80 select-none">KURT ALEJANDRO</div>
        <h2 class="mt-1 text-xl font-extrabold tracking-[0.2em] select-none">BOLIM GFX</h2>

        <div class="mt-6 mx-auto w-32 h-32 rounded-full ring-2 ring-white/15 overflow-hidden shadow-[0_12px_60px_rgba(0,0,0,.45)]">
          <img src="assets/images/profile.jpg" alt="Kurt Alejandro (Bolim)" class="w-full h-full object-cover select-none" />
        </div>

        <p class="mt-5 text-white/80 leading-relaxed select-none">
          <span class="font-semibold select-none">Kurt Alejandro</span>, also known as <span class="font-semibold select-none">Bolim</span>, is a graphic designer from Lipa, Batangas.
          Passionate about transforming ideas into visuals that tell stories, connect with people, and spark emotions through bold graphics,
          clean layouts, and creative concepts.
        </p>

        <!-- UPDATED: socials row w/ brand colors -->
        <div class="mt-5 flex items-center justify-center gap-3">
          <!-- Instagram (gradient) -->
          <a href="https://instagram.com/bolimgfx" target="_blank" rel="noopener"
             class="w-9 h-9 grid place-items-center rounded-full text-white font-bold
                    bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF]"
             aria-label="Instagram">IG</a>

          <!-- Facebook / Messenger (blue) -->
          <a href="https://m.me/kalejandrooo" target="_blank" rel="noopener"
             class="w-9 h-9 grid place-items-center rounded-full text-white font-bold
                    bg-[#1877F2]"
             aria-label="Facebook / Messenger">f</a>

          <!-- Behance (Behance blue) -->
          <a href="https://www.behance.net/kurtalejandro" target="_blank" rel="noopener"
             class="w-9 h-9 grid place-items-center rounded-full text-white font-bold
                    bg-[#1769FF]"
             aria-label="Behance">Bē</a>
        </div>
      </div>

      <!-- Contact form -->
      <form id="contactForm" class="mt-8 grid gap-5"
            action="mailto:you@example.com" method="post" enctype="text/plain">
        <input type="text" name="company" class="hidden" tabindex="-1" autocomplete="off"> <!-- honeypot -->

        <div>
          <label class="block text-sm mb-2 opacity-80 select-none">Name *</label>
          <input required name="Name" type="text"
                 class="w-full rounded-md bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-white/20"
                 placeholder="Your Name..." />
        </div>

        <div>
          <label class="block text-sm mb-2 opacity-80 select-none">Email Address *</label>
          <input required name="Email" type="email"
                 class="w-full rounded-md bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-white/20"
                 placeholder="your@email.com" />
        </div>

        <div>
          <label class="block text-sm mb-2 opacity-80 select-none">Message *</label>
          <textarea required name="Message" rows="6"
                    class="w-full rounded-md bg-white/5 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-white/20 resize-y"
                    placeholder="Your message..."></textarea>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="btn bg-white text-black px-6">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- SOCIAL LINKS MODAL -->
<div id="socialOverlay" aria-hidden="true">
  <div class="social-card relative mx-4">
    <button type="button" class="social-close" aria-label="Close" data-close-social>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M6 6l12 12M18 6L6 18" stroke="white" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </button>

    <div class="px-6 sm:px-8 pt-8 pb-6 text-center">
      <div class="text-[10px] tracking-[0.4em] uppercase opacity-80 select-none">BOLIM GFX</div>
      <h2 class="mt-1 text-xl font-extrabold tracking-[0.2em] select-none">SOCIAL LINKS</h2>

      <div class="mt-6 mx-auto w-24 h-24 rounded-full ring-2 ring-white/15 overflow-hidden shadow-[0_12px_60px_rgba(0,0,0,.45)]">
        <img src="assets/images/profile.jpg" alt="Kurt Alejandro (Bolim)" class="w-full h-full object-cover select-none" />
      </div>

      <p class="mt-4 text-white/70 text-sm select-none">Connect with me on social:</p>

      <div class="mt-6 grid gap-3 text-sm">
        <!-- Facebook / Messenger -->
        <a href="https://m.me/kalejandrooo" target="_blank" rel="noopener"
           class="w-full rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition">
          <div class="px-4 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-8 h-8 grid place-items-center rounded-full text-white text-sm font-bold bg-[#1877F2]">f</span>
              <span class="font-semibold whitespace-nowrap">Facebook / Messenger</span>
            </div>
            <span class="opacity-70 text-xs md:text-sm truncate">m.me/kalejandrooo</span>
          </div>
        </a>

        <!-- Instagram (gradient badge) -->
        <a href="https://instagram.com/bolimgfx" target="_blank" rel="noopener"
           class="w-full rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition">
          <div class="px-4 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-8 h-8 grid place-items-center rounded-full text-white text-sm font-bold
                           bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF]">IG</span>
              <span class="font-semibold whitespace-nowrap">Instagram</span>
            </div>
            <span class="opacity-70 text-xs md:text-sm truncate">@bolimgfx</span>
          </div>
        </a>

        <!-- Behance (Behance blue badge) -->
        <a href="https://www.behance.net/kurtalejandro" target="_blank" rel="noopener"
           class="w-full rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition">
          <div class="px-4 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-8 h-8 grid place-items-center rounded-full text-white text-sm font-bold bg-[#1769FF]">Bē</span>
              <span class="font-semibold whitespace-nowrap">Behance</span>
            </div>
            <span class="opacity-70 text-xs md:text-sm truncate">behance.net/kurtalejandro</span>
          </div>
        </a>
      </div>

    </div>
  </div>
</div>

<footer class="border-t border-white/5">
  <p class="text-center text-[11px] md:text-[12px] tracking-[0.25em] uppercase text-white/40 py-4 select-none">
    Copyright © <?= date('Y'); ?> BOLIM GFX.
  </p>
</footer>

<!-- LIGHTBOX (modal) -->
<div id="lightbox" class="lightbox" aria-hidden="true">
  <button class="lb-close" id="lbClose" aria-label="Close">✕</button>
  <button class="lb-btn lb-prev" id="lbPrev" aria-label="Previous">‹</button>
  <div class="lightbox-inner">
    <div class="lightbox-img-wrap">
      <img id="lbImg" class="lightbox-img" alt="">
      <div class="lb-guard"></div>
    </div>
    <div id="lbCap" class="lightbox-caption"></div>
  </div>
  <button class="lb-btn lb-next" id="lbNext" aria-label="Next">›</button>
</div>

<button id="toTopBtn" aria-label="Back to Artworks">
  <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <path d="M6 14l6-6 6 6" stroke="white" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</button>


<script>
/* Intro → show artworks */
const scrollBtn=document.getElementById('scrollBtn'), welcome=document.getElementById('welcome'), artworks=document.getElementById('artworks'), header=document.getElementById('siteHeader');
let welcomeHidden=false; header.style.opacity="0"; header.style.pointerEvents="none"; header.style.transition="opacity .6s ease";
function hideWelcomeAndScroll(){ if(!welcomeHidden){ welcome.classList.add('fade-out'); welcomeHidden=true;
  setTimeout(()=>{ welcome.style.display='none'; header.style.opacity="1"; header.style.pointerEvents="auto";
    const top=artworks.getBoundingClientRect().top+window.scrollY-header.offsetHeight; window.scrollTo({top,behavior:'smooth'}); },800); } }
scrollBtn.addEventListener('click',hideWelcomeAndScroll);
window.addEventListener('scroll',()=>{ if(window.scrollY>window.innerHeight/5 && !welcomeHidden){ welcome.classList.add('fade-out'); setTimeout(()=>{ welcome.style.display='none'; header.style.opacity="1"; header.style.pointerEvents="auto"; },800); welcomeHidden=true; } });
document.getElementById('menuBtn')?.addEventListener('click',()=>document.getElementById('mobileMenu')?.classList.toggle('hidden'));

/* Anti-save (deterrents) */
document.addEventListener('contextmenu',e=>e.preventDefault(),{passive:false});
document.addEventListener('dragstart',e=>e.preventDefault(),{passive:false});
document.addEventListener('keydown',e=>{
  const k=e.key.toLowerCase();
  if((e.ctrlKey||e.metaKey) && ['s','u','p'].includes(k)) e.preventDefault();
},{passive:false});

/* --------- Low-quality display pipeline (client-side compress) ---------- */
async function makeCompressedDataURL(url, maxW, maxH, quality=0.6){
  const res = await fetch(url, {cache:'force-cache'});
  const blob = await res.blob();
  const objURL = URL.createObjectURL(blob);
  try{
    const bmp = await createImageBitmap(blob);
    const {width:ow, height:oh} = bmp;
    let w=ow, h=oh;
    const scale = Math.min(maxW/ow, maxH/oh, 1);
    w = Math.round(ow*scale); h = Math.round(oh*scale);
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const ctx = c.getContext('2d', { alpha: false, desynchronized: true });
    ctx.filter = 'contrast(0.98) saturate(0.95)';
    ctx.drawImage(bmp, 0, 0, w, h);
    return c.toDataURL('image/jpeg', quality);
  } finally {
    URL.revokeObjectURL(objURL);
  }
}

/* Apply to grid thumbnails */
(async function hydrateGrid(){
  const thumbs = document.querySelectorAll('.tile');
  for (const tile of thumbs){
    const url = tile.dataset.full;
    const img = tile.querySelector('img');
    try{
      const dataURL = await makeCompressedDataURL(url, 900, 900, 0.5);
      img.src = dataURL;
    }catch(e){
      img.src = url;
    }
  }
})();

/* ---------------------- Lightbox ---------------------- */
const lb=document.getElementById('lightbox'), lbImg=document.getElementById('lbImg'), lbCap=document.getElementById('lbCap');
const lbClose=document.getElementById('lbClose'), lbPrev=document.getElementById('lbPrev'), lbNext=document.getElementById('lbNext');
const tiles=[...document.querySelectorAll('.tile')]; let current=-1;

async function openLB(i){
  if(i<0||i>=tiles.length) return;
  current=i; const el=tiles[i];
  const url = el.dataset.full;
  lbCap.textContent = el.dataset.title;
  lb.classList.add('open'); lb.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden';
  lbImg.src = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";

  try{
    const dataURL = await makeCompressedDataURL(url, 1600, 1600, 0.6);
    lbImg.src = dataURL;
    lbImg.alt = el.dataset.title;
  }catch(e){
    lbImg.src = url;
  }
}
function closeLB(){ lb.classList.remove('open'); lb.setAttribute('aria-hidden','true'); document.body.style.overflow=''; lbImg.src=''; current=-1; }
function nextLB(){ if(current===-1) return; openLB((current+1)%tiles.length); }
function prevLB(){ if(current===-1) return; openLB((current-1+tiles.length)%tiles.length); }

tiles.forEach((t,i)=>{ t.addEventListener('click',()=>openLB(i)); t.addEventListener('keydown',e=>{ if(e.key==='Enter' || e.key===' ') openLB(i); }); });
lbClose.addEventListener('click',closeLB); lbNext.addEventListener('click',nextLB); lbPrev.addEventListener('click',prevLB);
lb.addEventListener('click',e=>{ if(!e.target.closest('.lightbox-inner,.lb-btn,.lb-close')) closeLB(); });
window.addEventListener('keydown',e=>{ if(!lb.classList.contains('open')) return;
  if(e.key==='Escape') closeLB(); if(e.key==='ArrowRight') nextLB(); if(e.key==='ArrowLeft') prevLB(); });
</script>

<script>
  // CONTACT OVERLAY LOGIC
  (function(){
    const overlay = document.getElementById('contactOverlay');
    const openers = document.querySelectorAll('[data-open-contact]');
    const closer  = overlay?.querySelector('[data-close-contact]');
    const card    = overlay?.querySelector('.overlay-card');

    function openOverlay(e){
      if(e) e.preventDefault();
      overlay?.classList.add('show');
      document.body.classList.add('no-scroll');
      const firstInput = overlay?.querySelector('input[name="Name"]');
      firstInput && firstInput.focus();
      overlay?.setAttribute('aria-hidden','false');
    }

    function closeOverlay(e){
      if(e) e.preventDefault();
      overlay?.classList.remove('show');
      document.body.classList.remove('no-scroll');
      overlay?.setAttribute('aria-hidden','true');
    }

    openers.forEach(a => a.addEventListener('click', openOverlay));
    closer?.addEventListener('click', closeOverlay);

    overlay?.addEventListener('click', (e) => {
      if(!card.contains(e.target)) closeOverlay();
    });

    document.addEventListener('keydown', (e)=>{
      if(e.key === 'Escape' && overlay?.classList.contains('show')) closeOverlay();
    });

    const form = document.getElementById('contactForm');
    form?.addEventListener('submit', (e) => {
      const name  = form.querySelector('[name="Name"]')?.value.trim();
      const email = form.querySelector('[name="Email"]')?.value.trim();
      const msg   = form.querySelector('[name="Message"]')?.value.trim();
      const hp    = form.querySelector('[name="company"]')?.value.trim();
      if (hp) { e.preventDefault(); return; }
      if (!name || !email || !msg) {
        e.preventDefault();
        alert('Please complete all required fields.');
      }
    });
  })();
</script>

<script>
  // SOCIAL OVERLAY LOGIC
  (function(){
    const overlay = document.getElementById('socialOverlay');
    const openers = document.querySelectorAll('[data-open-social]');
    const closer  = overlay?.querySelector('[data-close-social]');
    const card    = overlay?.querySelector('.social-card');

    function openSocial(e){
      if(e) e.preventDefault();
      overlay?.classList.add('show');
      document.body.classList.add('no-scroll');
      overlay?.setAttribute('aria-hidden','false');
    }
    function closeSocial(e){
      if(e) e.preventDefault();
      overlay?.classList.remove('show');
      document.body.classList.remove('no-scroll');
      overlay?.setAttribute('aria-hidden','true');
    }

    openers.forEach(el => el.addEventListener('click', openSocial));
    closer?.addEventListener('click', closeSocial);

    overlay?.addEventListener('click', (e)=>{
      if (!card.contains(e.target)) closeSocial();
    });

    document.addEventListener('keydown', (e)=>{
      if (e.key === 'Escape' && overlay?.classList.contains('show')) closeSocial();
    });
  })();
</script>

<script>
(function(){
  const toTopBtn = document.getElementById('toTopBtn');
  const artworks = document.getElementById('artworks');
  const header   = document.getElementById('siteHeader');
  const lightbox = document.getElementById('lightbox');
  const contactOverlay = document.getElementById('contactOverlay');
  const socialOverlay  = document.getElementById('socialOverlay');

  // show/hide logic
  let ticking = false;
  function onScroll(){
    if(ticking) return;
    ticking = true;
    requestAnimationFrame(()=>{
      const trigger = (window.scrollY > (artworks.offsetTop + 300));
      const modalOpen = (lightbox?.classList.contains('open')
                         || contactOverlay?.classList.contains('show')
                         || socialOverlay?.classList.contains('show'));
      if (trigger && !modalOpen){
        toTopBtn.classList.add('show');
      } else {
        toTopBtn.classList.remove('show');
      }
      ticking = false;
    });
  }
  window.addEventListener('scroll', onScroll, {passive:true});
  onScroll();

  // click → smooth scroll sa taas ng artworks (minus sticky header)
  toTopBtn.addEventListener('click', (e)=>{
    e.preventDefault();
    const top = artworks.getBoundingClientRect().top + window.scrollY - header.offsetHeight;
    window.scrollTo({ top, behavior: 'smooth' });
  });

  // kapag nagbukas ng modal/lightbox, itago
  ['open','show'].forEach(cls=>{
    new MutationObserver(onScroll).observe(document.body,{attributes:true,subtree:true,attributeFilter:['class']});
  });
})();
</script>

</body>
</html>
