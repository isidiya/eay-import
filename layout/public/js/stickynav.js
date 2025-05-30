//smooth scroll that matches any href to an associated section id

/* $(function() {
     $('a[href*=#]:not([href=#])').click(function() {
       var target = $(this.hash);
     if (location.hostname == this.hostname) {
       if (target.length !=0) {
             $('html,body').animate({
                  scrollTop: target.offset().top
             }, 1000);
               return false;
           }
     }
   });
 });*/

 /*get information like widths, coordinates, and heights
   on page load. then call stickyNav() and addActive() on window scroll */
 $(document).ready(function() {
   var stickyNavTop = $('nav').offset().top,
       window_height = $(window).height(),
       nav = $('nav'),
       nav_height = nav.outerHeight(),
       doc_height = $(document).height();

   $(window).scroll(function() {
       stickyNav();
       addActive();

   });
   //add 'sticking' functionality-- position fixed
   function stickyNav(){
     var scrollTop = $(window).scrollTop();
     if (scrollTop > stickyNavTop) {
         $('#nav').addClass('sticky');
     } else {
         $('#nav').removeClass('sticky');
     }

     // stop sticky functionality before overlapping with footer ** Rana **
     if(doc_height - window_height < scrollTop){
       $('#nav').addClass('stickBottom');
     }else{
       $('#nav').removeClass('stickBottom');
     }
   };
   // add active class to identify what section is currently being selected
   function addActive() {
     var scrollPos = $(window).scrollTop();
     $('.section').each(function(){
       var top = $(this).offset().top - nav_height,
           bottom = top + $(this).outerHeight();
       if (scrollPos >= top && scrollPos <= bottom) {
         nav.find('a').removeClass('active');
         nav.find('a[href="#'+ $(this).attr('id') + '"]').addClass('active');
       }
       else if (scrollPos + window_height == doc_height) {
         if (!$('#nav a:last').hasClass('active')) {
           nav.find('a').removeClass('active');
           nav.find('a:last').addClass('active');
         }
       }
     });
   }
 });