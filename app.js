(function(){
  var d=document.documentElement,b=document.getElementById('burg'),nav=document.getElementById('nav');
  function zamknijMenu(){d.classList.remove('menu-open');b.setAttribute('aria-expanded','false');b.setAttribute('aria-label','Otwórz menu');}
  b.addEventListener('click',function(){var o=d.classList.toggle('menu-open');if(o)nav.querySelectorAll('.grp.akt').forEach(function(g){g.classList.add('otw');g.querySelector('.grp-b').setAttribute('aria-expanded','true');});b.setAttribute('aria-expanded',o);b.setAttribute('aria-label',o?'Zamknij menu':'Otwórz menu');});
  nav.querySelectorAll('a').forEach(function(a){a.addEventListener('click',zamknijMenu);});
  // podmenu: klik otwiera/zamyka, klik obok i Esc zamyka
  var grupy=nav.querySelectorAll('.grp');
  grupy.forEach(function(g){
    var p=g.querySelector('.grp-b');
    p.addEventListener('click',function(e){
      e.stopPropagation();
      var o=!g.classList.contains('otw');
      if(window.innerWidth>980)grupy.forEach(function(x){x.classList.remove('otw');x.querySelector('.grp-b').setAttribute('aria-expanded','false');});
      g.classList.toggle('otw',o);p.setAttribute('aria-expanded',o);
    });
  });
  document.addEventListener('click',function(e){if(window.innerWidth>980&&!nav.contains(e.target))grupy.forEach(function(g){g.classList.remove('otw');g.querySelector('.grp-b').setAttribute('aria-expanded','false');});});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'){grupy.forEach(function(g){if(window.innerWidth>980){g.classList.remove('otw');g.querySelector('.grp-b').setAttribute('aria-expanded','false');}});zamknijMenu();}});

  // pasek mobilny po przewinieciu za pierwszy ekran
  var mob=document.getElementById('mob'),od=document.querySelector('[data-mob-od]'),kon=document.getElementById('kontakt');
  if('IntersectionObserver' in window&&mob&&od){
    var zaOd=false,wKont=false;
    function pokaz(){mob.classList.toggle('widac',zaOd&&!wKont);}
    new IntersectionObserver(function(e){zaOd=!e[0].isIntersecting&&e[0].boundingClientRect.top<0;pokaz();},{threshold:0}).observe(od);
    if(kon)new IntersectionObserver(function(e){wKont=e[0].isIntersecting;pokaz();},{threshold:.15}).observe(kon);
  }

  // mapa Google dopiero po kliknieciu
  document.querySelectorAll('[data-mapa]').forEach(function(p){
    p.addEventListener('click',function(){
      var m=document.getElementById('mapa'),f=document.createElement('iframe');
      f.src='https://maps.google.com/maps?q='+encodeURIComponent(p.getAttribute('data-mapa'))+'&z=14&hl=pl&output=embed';
      f.title='Mapa dojazdu - '+p.getAttribute('data-mapa');f.referrerPolicy='no-referrer-when-downgrade';f.allowFullscreen=true;
      m.innerHTML='';m.classList.add('zal');m.appendChild(f);
    });
  });

  // pas kart na glownej: przeciaganie mysza
  var pas=document.getElementById('pas');
  if(pas){
    var x0=0,s0=0,ciag=false,ruch=0;
    pas.addEventListener('pointerdown',function(e){if(e.pointerType!=='mouse'||pas.scrollWidth<=pas.clientWidth)return;ciag=true;ruch=0;x0=e.clientX;s0=pas.scrollLeft;pas.style.scrollSnapType='none';});
    window.addEventListener('pointermove',function(e){if(!ciag)return;ruch=Math.abs(e.clientX-x0);pas.scrollLeft=s0-(e.clientX-x0);});
    window.addEventListener('pointerup',function(){if(!ciag)return;ciag=false;pas.style.scrollSnapType='';});
    pas.addEventListener('click',function(e){if(ruch>6){e.preventDefault();e.stopPropagation();}},true);
    pas.addEventListener('dragstart',function(e){e.preventDefault();});
  }

  // formularz - na podgladzie bez wysylki
  var form=document.getElementById('form');
  if(form)form.addEventListener('submit',function(e){
    e.preventDefault();
    var ok=true;
    form.querySelectorAll('[required]').forEach(function(p){var z=!p.value.trim();p.setAttribute('aria-invalid',z);if(z)ok=false;});
    var k=document.getElementById('komunikat');
    if(!ok){k.className='uwaga cala bl';k.textContent='Uzupełnij imię i dane kontaktowe.';return;}
    k.className='uwaga cala';
    k.textContent='Podgląd strony: wiadomość nie została wysłana. Na gotowej stronie zapytanie trafi na involte@involte.pl.';
  });

  // powiekszenie wykresow i zdjec
  var lb=null;
  document.querySelectorAll('a.pow').forEach(function(a){
    a.addEventListener('click',function(e){
      if(!window.HTMLDialogElement)return;
      e.preventDefault();
      if(!lb){lb=document.createElement('dialog');lb.className='lb';lb.innerHTML='<img alt=""><button type="button" aria-label="Zamknij">×</button>';document.body.appendChild(lb);
        lb.addEventListener('click',function(ev){if(ev.target!==lb.querySelector('img'))lb.close();});}
      var i=lb.querySelector('img');i.src=a.getAttribute('href');i.alt=(a.querySelector('img')||{}).alt||'';
      lb.showModal();
    });
  });
})();
