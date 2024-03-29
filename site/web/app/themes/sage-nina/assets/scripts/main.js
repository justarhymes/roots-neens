/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 * ======================================================================== */

(function($) {

  // Use this variable to set up the common and page specific functions. If you
  // rename this variable, you will also need to rename the namespace below.
  var Sage = {
    // All pages
    'common': {
      init: function() {

        function launchEvents() {
          // JavaScript to be fired on all pages
          var pageNum = 1;
          var nextNum;
          var categories = [];
          var tags = [];
          var ajaxLink;
          var fullLink;
          var pathname = window.location.pathname.split('/');
          var first_path = pathname[1];
          var second_path = pathname[2];
          var currentCatNum;
          var currentTagNum;

          $.ajax({
            url: '/wp-json/wp/v2/categories',
            type: 'GET',
            success: function(data) {
              if (data) {
                categories = data;
                if (first_path === 'art') {
                  for (var i; i > data.length; i++) {
                    if (second_path === data[i].slug) {
                      currentCatNum = data[i].id;
                    }
                  }
                }
              }
            }
          });

          $.ajax({
            url: '/wp-json/wp/v2/tags',
            type: 'GET',
            success: function(data) {
              if (data) {
                tags = data;
                if (first_path === 'tag') {
                  for (var i; i > data.length; i++) {
                    if (second_path === data[i].slug) {
                      currentTagNum = data[i].id;
                    }
                  }
                }
              }
            }
          });

          $('.loadmore').click(function() {
            var button = $(this);
            var ajaxLink = '/wp-json/wp/v2/posts?per_page=18&page=' + nextNum;
            nextNum = pageNum + 1;

            if (first_path === 'art') {
              fullLink = ajaxLink + '&categories=' + currentCatNum;
            } else if (first_path === 'tag') {
              fullLink = ajaxLink + '&tags=' + currentTagNum;
            } else if (first_path === '') {
              fullLink = ajaxLink;
            }

            $.ajax({
              url: '/wp-json/wp/v2/posts?per_page=18&page=' + nextNum,
              type: 'GET',
              beforeSend: function(xhr) {
                button.text('Loading...');
              },
              success: function(data, textStatus, request) {
                if (data) {
                  var totalPosts = request.getResponseHeader('X-WP-Total');
                  var numberOfPages = Math.ceil(totalPosts / 18);

                  if (numberOfPages > pageNum + 1) {
                    pageNum++;
                    button.text('More posts');
                  } else {
                    button.remove();
                  }

                  for (var i = 0; i < data.length; i++) {
                    var cat = {};
                    for (var c = 0; c < categories.length; c++) {
                      if (data[i].categories[0] === categories[c].id) {
                        cat = categories[c];
                      }
                    }
                    var mediaLink = data[i].better_featured_image.media_details.sizes.thumbnail.source_url;
                    var html = '<article class="post col-xl-4 col-md-6">' +
                              '<div class="entry">' +
                                '<header>' +
                                  '<a href="/' + data[i].slug + '">' +
                                    '<h2 class="entry-title">' + data[i].title.rendered + '</h2>' +
                                    '<div class="entry-meta">In <span class="cat">' + cat.name + '</span></div>' +
                                    '<div class="bg"></div>' +
                                  '</a>' +
                                '</header>' +
                                '<div class="entry-image">' +
                                  '<img src="' + mediaLink + '" class="img-responsive b-lazy wp-post-image" alt="">' +
                                '</div>' +
                              '</div>' +
                            '</article>';
                    $('#posts-content').append(html);
                  }
                } else {
                  button.remove();
                }
              }
            });
          });
        }

        launchEvents();
      },
      finalize: function() {
        // JavaScript to be fired on all pages, after page specific JS is fired
      }
    },
    // Home page
    'home': {
      init: function() {
        // JavaScript to be fired on the home page
      },
      finalize: function() {
        // JavaScript to be fired on the home page, after the init JS
      }
    },
    // About us page, note the change from about-us to about_us.
    'about_us': {
      init: function() {
        // JavaScript to be fired on the about us page
      }
    }
  };

  // The routing fires all common scripts, followed by the page specific scripts.
  // Add additional events for more control over timing e.g. a finalize event
  var UTIL = {
    fire: function(func, funcname, args) {
      var fire;
      var namespace = Sage;
      funcname = (funcname === undefined) ? 'init' : funcname;
      fire = func !== '';
      fire = fire && namespace[func];
      fire = fire && typeof namespace[func][funcname] === 'function';

      if (fire) {
        namespace[func][funcname](args);
      }
    },
    loadEvents: function() {
      // Fire common init JS
      UTIL.fire('common');

      // Fire page-specific init JS, and then finalize JS
      $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
        UTIL.fire(classnm);
        UTIL.fire(classnm, 'finalize');
      });

      // Fire common finalize JS
      UTIL.fire('common', 'finalize');
    }
  };

  // Load Events
  $(document).ready(UTIL.loadEvents);

})(jQuery); // Fully reference jQuery after this point.
