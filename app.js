(function(){
  var b=document.getElementById('burg'),d=document.documentElement;
  b.addEventListener('click',function(){var o=d.classList.toggle('menu-open');b.setAttribute('aria-expanded',o);b.setAttribute('aria-label',o?'Zamknij menu':'Otwórz menu');});
  document.querySelectorAll('#nav a').forEach(function(a){a.addEventListener('click',function(){d.classList.remove('menu-open');b.setAttribute('aria-expanded','false');});});
  // pasek mobilny po przewinieciu za pierwszy ekran
  var mob=document.getElementById('mob'),hero=document.getElementById('start');
  if('IntersectionObserver' in window){
    new IntersectionObserver(function(e){mob.classList.toggle('widac',!e[0].isIntersecting);},{threshold:0}).observe(hero.querySelector('.btn'));
    var k=document.getElementById('kontakt');
    new IntersectionObserver(function(e){if(e[0].isIntersecting)mob.classList.remove('widac');},{threshold:.2}).observe(k);
    // mapa ladowana przy przewinieciu
    var m=document.getElementById('mapa');
    new IntersectionObserver(function(e,o){if(e[0].isIntersecting){var f=document.createElement('iframe');f.src=m.dataset.src;f.title='Mapa dojazdu - Gwiaździsta 57, Swarzędz';f.loading='lazy';f.referrerPolicy='no-referrer-when-downgrade';m.appendChild(f);o.disconnect();}},{rootMargin:'300px'}).observe(m);
  }
  // pas kart: przeciaganie mysza
  var p=document.getElementById('pas'),x0=0,s0=0,ciag=false,ruch=0;
  p.addEventListener('pointerdown',function(e){if(e.pointerType!=='mouse'||p.scrollWidth<=p.clientWidth)return;ciag=true;ruch=0;x0=e.clientX;s0=p.scrollLeft;p.style.scrollSnapType='none';});
  window.addEventListener('pointermove',function(e){if(!ciag)return;ruch=Math.abs(e.clientX-x0);p.scrollLeft=s0-(e.clientX-x0);});
  window.addEventListener('pointerup',function(){if(!ciag)return;ciag=false;p.style.scrollSnapType='';});
  p.addEventListener('click',function(e){if(ruch>6){e.preventDefault();e.stopPropagation();}},true);
  p.addEventListener('dragstart',function(e){e.preventDefault();});
  // formularz pokazowy
  document.getElementById('form').addEventListener('submit',function(e){e.preventDefault();document.getElementById('komunikat').textContent='To podgląd projektu - na gotowej stronie wiadomość trafi prosto na involte@involte.pl.';});
})();
