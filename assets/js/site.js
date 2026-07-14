(function(){
  var header = document.querySelector('.site-header');
  var menuButton = document.querySelector('.burger');
  var navigation = document.querySelector('.nav-links');
  var ticking = false;

  function updateHeader(){
    if(header){ header.classList.toggle('header-scrolled', window.scrollY > 24); }
    ticking = false;
  }

  function requestHeaderUpdate(){
    if(!ticking){
      window.requestAnimationFrame(updateHeader);
      ticking = true;
    }
  }

  function setMenu(open){
    if(!menuButton || !navigation){ return; }
    navigation.classList.toggle('open', open);
    menuButton.setAttribute('aria-expanded', String(open));
    menuButton.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    header.classList.toggle('menu-open', open);
  }

  updateHeader();
  window.addEventListener('scroll', requestHeaderUpdate, {passive:true});

  if(menuButton && navigation){
    menuButton.addEventListener('click', function(){
      setMenu(menuButton.getAttribute('aria-expanded') !== 'true');
    });
    navigation.addEventListener('click', function(event){
      if(event.target.closest('a')){ setMenu(false); }
    });
    document.addEventListener('keydown', function(event){
      if(event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true'){
        setMenu(false);
        menuButton.focus();
      }
    });
    window.addEventListener('resize', function(){
      if(window.innerWidth > 680){ setMenu(false); }
    });
  }
})();

(function(){
  var tickerTrack = document.querySelector('.ticker-track');
  var tickerGroup = tickerTrack && tickerTrack.querySelector('.ticker-group');
  if(tickerTrack && tickerGroup){
    var duplicate = tickerGroup.cloneNode(true);
    duplicate.setAttribute('aria-hidden', 'true');
    tickerTrack.appendChild(duplicate);
    tickerTrack.classList.add('is-ready');
  }
})();

document.querySelectorAll('section .wrap > *, .media .wrap > *').forEach(function(el){el.classList.add('reveal')});
if('IntersectionObserver' in window){
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} });
  },{threshold:.12});
  document.querySelectorAll('.reveal').forEach(function(el){io.observe(el)});
}else{
  document.querySelectorAll('.reveal').forEach(function(el){el.classList.add('in')});
}
