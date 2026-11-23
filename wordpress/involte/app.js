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

  // zgoda cookies (localStorage, 12 mies.) i mapa Google
  var KLUCZ='involte-zgoda',cb=document.getElementById('ciastka'),ck=document.getElementById('c-kat'),cm=document.getElementById('c-mapy');
  function zgoda(){try{var z=JSON.parse(localStorage.getItem(KLUCZ));if(z&&Date.now()-z.t<31536000000)return z;}catch(e){}return null;}
  function zapiszZgode(mapy){try{localStorage.setItem(KLUCZ,JSON.stringify({t:Date.now(),mapy:!!mapy}));}catch(e){}if(cb)cb.hidden=true;if(mapy)mapaPoZgodzie();}
  function wczytajMape(q){
    var m=document.getElementById('mapa');if(!m||m.classList.contains('zal'))return;
    var f=document.createElement('iframe');
    f.src='https://maps.google.com/maps?q='+encodeURIComponent(q)+'&z=14&hl=pl&output=embed';
    f.title='Mapa dojazdu - '+q;f.referrerPolicy='no-referrer-when-downgrade';f.allowFullscreen=true;f.loading='lazy';
    m.innerHTML='';m.classList.add('zal');m.appendChild(f);
  }
  function mapaPoZgodzie(){
    var m=document.getElementById('mapa');if(!m)return;
    var q=(m.querySelector('[data-mapa]')||{getAttribute:function(){return 'Gwiaździsta 57, Swarzędz';}}).getAttribute('data-mapa');
    if('IntersectionObserver' in window){var io=new IntersectionObserver(function(e){if(e[0].isIntersecting){io.disconnect();wczytajMape(q);}},{rootMargin:'300px'});io.observe(m);}
    else wczytajMape(q);
  }
  document.querySelectorAll('[data-mapa]').forEach(function(p){
    p.addEventListener('click',function(){var q=p.getAttribute('data-mapa');zapiszZgode(true);wczytajMape(q);});
  });
  document.querySelectorAll('[data-c]').forEach(function(p){
    p.addEventListener('click',function(){
      var c=p.getAttribute('data-c');
      if(c==='wszystkie')zapiszZgode(true);
      else if(c==='niezbedne')zapiszZgode(false);
      else if(c==='zapisz')zapiszZgode(cm&&cm.checked);
      else if(c==='ustawienia'||c==='otworz'){
        var z=zgoda();if(cm)cm.checked=!!(z&&z.mapy);
        cb.hidden=false;ck.hidden=false;cb.querySelector('[data-c="zapisz"]').hidden=false;cb.querySelector('[data-c="ustawienia"]').hidden=true;
        cb.querySelector('[data-c="zapisz"]').focus();
      }
    });
  });
  var z0=zgoda();if(z0&&z0.mapy)mapaPoZgodzie();

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

  // formularz: wysylka do formularz.php
  var form=document.getElementById('form');
  if(form){
    var ts=form.querySelector('[name=ts]');if(ts)ts.value=Math.floor(Date.now()/1000);
    form.addEventListener('submit',function(e){
      e.preventDefault();
      var ok=true;
      form.querySelectorAll('[required]').forEach(function(p){var z=!p.value.trim();p.setAttribute('aria-invalid',z);if(z)ok=false;});
      var k=document.getElementById('komunikat'),bt=form.querySelector('button[type=submit]');
      if(!ok){k.className='uwaga cala bl';k.textContent='Uzupełnij imię i dane kontaktowe.';return;}
      var fd=new FormData(form);fd.append('strona',location.pathname);
      bt.disabled=true;k.className='uwaga cala';k.textContent='Wysyłanie...';
      fetch(form.action,{method:'POST',body:fd,headers:{'Accept':'application/json'}}).then(function(r){return r.json().catch(function(){return {ok:false};});}).then(function(d){
        bt.disabled=false;
        if(d&&d.ok){form.reset();if(ts)ts.value=Math.floor(Date.now()/1000);k.className='uwaga cala k-ok';k.textContent='Dziękujemy, zapytanie zostało wysłane. Odpowiemy najszybciej, jak to możliwe.';}
        else{k.className='uwaga cala bl';k.textContent=(d&&d.blad)||'Nie udało się wysłać wiadomości. Zadzwoń: 570 083 207 albo napisz na involte@involte.pl.';}
      }).catch(function(){bt.disabled=false;k.className='uwaga cala bl';k.textContent='Nie udało się wysłać wiadomości. Zadzwoń: 570 083 207 albo napisz na involte@involte.pl.';});
    });
  }

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
