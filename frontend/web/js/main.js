$(document).ready(function(){
   console.log("Inited");
   let sidebarContainer = $('.sidebar.glass-panel');
   let navContainer = $('.nav-list');
   $('.nav-list__triangle').click(function(){
      $(sidebarContainer).toggleClass('open');
      $(this).toggleClass('closed');
   });
});