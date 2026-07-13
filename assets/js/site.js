(function(){
  var header = document.querySelector('header');
  function updateHeader(){
    if(header){ header.classList.toggle('header-scrolled', window.scrollY > 24); }
  }
  updateHeader();
  window.addEventListener('scroll', updateHeader, {passive:true});
})();

(function(){
  var tickerTrack = document.querySelector('.ticker-track');
  if(tickerTrack){ tickerTrack.innerHTML += tickerTrack.innerHTML; }
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
