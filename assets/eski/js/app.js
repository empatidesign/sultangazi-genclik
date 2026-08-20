window.theme = window.theme || {};

if ($(".swiper-hero").length > 0) {
  const swiperHero = new Swiper(".swiper-hero", {
    loop: false,
    spaceBetween: 20,
    centeredSlides: true,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
      clickable: true,
    },
    autoplay: {
      delay: 3000,
    }
  });
}

if ($(".swiper-hero-mobile").length > 0) {
  const swiperHeroMobile = new Swiper(".swiper-hero-mobile", {
    loop: false,
    spaceBetween: 20,
    centeredSlides: true,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
    },
    autoplay: {
      delay: 3000,
    },
  });
}

if ($(".swiper-index-mini").length > 0) {
  const indexMiniSlider = new Swiper(".swiper-index-mini", {
    spaceBetween: 1,
    slidesPerView: 3,
    centeredSlides: true,
    //roundLengths: true,
    loop: true,
    //loopAdditionalSlides: 30,
    slideToClickedSlide: true,
    // mousewheel: true,
    keyboard: true,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
    },
    autoplay: {
      delay: 3000,
    },
    breakpoints: {
      320: {
        slidesPerView: 1,
      },
      480: {
        slidesPerView: 2,
      },
      640: {
        slidesPerView: 3,
      },
    },
  });
}

if ($(".gallery-thumbs").length > 0) {
  let galleryThumbs = new Swiper(".gallery-thumbs", {
    spaceBetween: 10,
    slidesPerView: 8,
    freeMode: true,
    loopedSlides: 1,
    watchSlidesVisibility: true,
    watchSlidesProgress: true,
    preloadImages: false,
    centered: false,
    breakpoints: {
      320: {
        slidesPerView: 3,
      },
      480: {
        slidesPerView: 4,
      },
      640: {
        slidesPerView: 5,
      },
    },
  });

  let galleryTop = new Swiper(".gallery-top", {
    spaceBetween: 1,
    loopedSlides: 1,
    thumbs: {
      swiper: galleryThumbs,
    },
    preloadImages: false,
    lazy: true,
  });
}

if ($(".swiper-shortcuts").length > 0) {
  const swiperShortcuts = new Swiper(".swiper-shortcuts", {
    loop: false,
    autoplay: true,
    spaceBetween: 15,
    navigation: {
      nextEl: ".swiper-shortcuts .swiper-button-next",
      prevEl: ".swiper-shortcuts .swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
      clickable: true,
    },
    breakpoints: {
      320: {
        slidesPerView: 2,
      },
      640: {
        slidesPerView: 3,
      },
      768: {
        slidesPerView: 4,
      },
      990: {
        slidesPerView: 6,
      },
      1200: {
        slidesPerView: 8,
      },
    },
  });
}

if ($(".swiper-news-mobile").length > 0) {
  const swiperNewsMobile = new Swiper(".swiper-news-mobile", {
    loop: false,
    spaceBetween: 15,
    breakpoints: {
      320: {
        slidesPerView: 1,
      },
      480: {
        slidesPerView: 2,
      },
      640: {
        slidesPerView: 3,
      },
      992: {
        slidesPerView: 4,
      },
    },
  });
}

if ($(".swiper-projects").length > 0) {
  const swiperProjects = new Swiper(".swiper-projects", {
    loop: false,
    spaceBetween: 25,
    breakpoints: {
      320: {
        slidesPerView: 1,
      },
      480: {
        slidesPerView: 1,
      },
      640: {
        slidesPerView: 2,
      },
      992: {
        slidesPerView: 3,
      },
      1068: {
        slidesPerView: 4,
      },
    },
  });
}

if ($(".swiper-events").length > 0) {
  const swiperEvents = new Swiper(".swiper-events", {
    loop: false,
    spaceBetween: 15,
    navigation: {
      nextEl: ".swiper-events .swiper-button-next",
      prevEl: ".swiper-events .swiper-button-prev",
    },
    breakpoints: {
      320: {
        slidesPerView: 1,
      },
      480: {
        slidesPerView: 1,
      },
      640: {
        slidesPerView: 2,
      },
      992: {
        slidesPerView: 3,
      },
      1200: {
        slidesPerView: 4,
      },
    },
  });
}

if ($(".swiper-company").length > 0) {
  const swiperCompanies = new Swiper(".swiper-company", {
    loop: false,
    slidesPerView: true,
    spaceBetween: 15,
    navigation: {
      nextEl: ".swiper-shortcuts .swiper-button-next",
      prevEl: ".swiper-shortcuts .swiper-button-prev",
    },
	/*
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
      clickable: true,
    },
	*/
    breakpoints: {
      320: {
        slidesPerView: 1,
      },
      480: {
        slidesPerView: 1,
      },
      640: {
        slidesPerView: 3,
      },
      992: {
        slidesPerView: 6,
      },
    },
  });
}

if ($(".swiper-hero-company").length > 0) {
  const swiperHeroCompanies = new Swiper(".swiper-hero-company", {
    loop: false,
    spaceBetween: 15,
    centeredSlides: true,
	/*
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
      clickable: true,
    },
	*/
    breakpoints: {
      320: {
        slidesPerView: 1,
      },
      480: {
        slidesPerView: 1,
      },
      640: {
        slidesPerView: 1,
      },
      992: {
        slidesPerView: 1,
      }
    },
    autoplay: {
      delay: 3000,
    }
  });
}

if ($(".swiper-hero-company-mobile").length > 0) {
  const swiperHeroCompaniesMobile = new Swiper(".swiper-hero-company-mobile", {
    loop: true,
    spaceBetween: 15,
    centeredSlides: true,
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
    },
    slidesPerView: 2,
  });
}

if ($(".swiper-announcement").length > 0) {
  setTimeout(() => {
    const swiper2 = new Swiper(".swiper-announcement", {
      loop: false,
      spaceBetween: 0,
      autoplay: {
        delay: 3000,
      },
    });
  }, 1000);
}

if ($(".swiper-multimedia-gallery").length > 0) {
  const swiperMultimediaGallery = new Swiper(".swiper-multimedia-gallery", {
    loop: true,
    spaceBetween: 15,
    centeredSlides: true,
    slidesPerView: 3,
    autoplay: {
      delay: 3500,
    },
  });
}

if ($(".swiper-multimedia-video").length > 0) {
  const swiperMultimediaVideo = new Swiper(".swiper-multimedia-video", {
    loop: true,
    spaceBetween: 15,
    centeredSlides: true,
    slidesPerView: 3,
    autoplay: {
      delay: 3500,
    },
  });
}

if ($(".swiper-multimedia-tour").length > 0) {
  const swiperMultimediaTour = new Swiper(".swiper-multimedia-tour", {
    loop: true,
    spaceBetween: 15,
    centeredSlides: true,
    slidesPerView: 3,
    autoplay: {
      delay: 3500,
    },
  });
}

if ($(".swiper-multimedia-gallery-mobile").length > 0) {
  const swiperMultimediaGalleryMobile = new Swiper(
    ".swiper-multimedia-gallery-mobile",
    {
      loop: true,
      spaceBetween: 15,
      slidesPerView: 2,
      autoplay: {
        delay: 1000,
      },
      pagination: {
        el: ".swiper-pagination",
        type: "bullets",
      },
    }
  );
}

if ($(".swiper-multimedia-video-mobile").length > 0) {
  const swiperMultimediaVideoMobile = new Swiper(
    ".swiper-multimedia-video-mobile",
    {
      loop: true,
      spaceBetween: 15,
      slidesPerView: 2,
      autoplay: {
        delay: 1000,
      },
    }
  );
}

if ($(".swiper-multimedia-tour-mobile").length > 0) {
  const swiperMultimediaTourMobile = new Swiper(
    ".swiper-multimedia-tour-mobile",
    {
      loop: true,
      spaceBetween: 15,
      slidesPerView: 2,
      autoplay: {
        delay: 1000,
      },
    }
  );
}

if ($(".swiper-services").length > 0) {
  const swiperServices = new Swiper(".swiper-services", {
    loop: true,
    spaceBetween: 15,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
      clickable: true,
    },
  });
}

let header = document.querySelector(".header");
let overlay = document.querySelector(".overlay-bg");
let navLinkOverlay = document.querySelectorAll(".nav-link-overlay");
let btnTopNavs = document.querySelectorAll(".btn-top-nav");
let btnRoundTopNavs = document.querySelectorAll(".btn-round-top-nav");
let navContentOverlay = document.querySelectorAll(".nav-content-overlay");
btnRoundTopNavs.forEach(function (item, i) {
  item.addEventListener("mouseover", function () {
    navContentOverlay.forEach(function (item, i) {
      item.classList.remove("show");
    });
    navLinkOverlay.forEach(function (item, i) {
      item.classList.remove("active");
    });
    header.classList.remove("show");
    return false;
  });
});

btnTopNavs.forEach(function (item, i) {
  item.addEventListener("mouseover", function () {
    navContentOverlay.forEach(function (item, i) {
      item.classList.remove("show");
    });
    navLinkOverlay.forEach(function (item, i) {
      item.classList.remove("active");
    });
    header.classList.remove("show");
    return false;
  });
});

navLinkOverlay.forEach(function (item, i) {
  var is_mobile = ($(item).parents('.mobile-sm').length)?true:false;
  if (is_mobile){
     $(item).removeAttr('href');
     item.addEventListener("click",function(){return false;});
  } else {
  item.addEventListener(((is_mobile)?"click":"mouseover"), function () {
    if (item.classList.contains("no-overlay")) {
      navContentOverlay.forEach(function (item, i) {
        item.classList.remove("show");
      });
      navLinkOverlay.forEach(function (item, i) {
        item.classList.remove("active");
      });
      header.classList.remove("show");
      return false;
    }

    navLinkOverlay.forEach(function (item, i) {
      item.classList.remove("active");
    });

    navContentOverlay.forEach(function (item, i) {
      item.classList.remove("show");
      item.addEventListener("mouseleave", function () {
        header.classList.remove("show");
        navLinkOverlay.forEach(function (item, i) {
          item.classList.remove("active");
        });
        navContentOverlay.forEach(function (item, i) {
          item.classList.remove("show");
        });
      });
    });

    item.classList.add("active");
    let id = this.dataset.target;

    let contentItem = document.querySelector("#" + id) || false;
    if (contentItem.legth > 0) {
	contentItem.classList.add("show"); 
    	header.classList.add("show");
    }
    return false;
  });
  }
});

let mobileNavLink = document.querySelectorAll(".mobile-nav-link");
let mobileHeaderContent = document.querySelector(".mobile-header-content");
mobileNavLink.forEach(function (item, i) {
  item.addEventListener("click", function () {
    mobileHeaderContent.style.display = "block";
    document.body.style.overflow = "hidden";
    mobileHeaderContent.style.setProperty("--animate-duration", "0.8s");
    mobileHeaderContent.classList.add(
      "animate__animated",
      "animate__fadeInLeft"
    );

    let btnModalClose = document.querySelector(".btn-modal-close");
    btnModalClose.addEventListener("click", function () {
      document.body.style.overflow = "scroll";

      mobileHeaderContent.style.setProperty("--animate-duration", "0.8s");
      mobileHeaderContent.classList.remove(
        "animate__animated",
        "animate__fadeInLeft"
      );

      mobileHeaderContent.classList.add(
        "animate__animated",
        "animate__fadeOutLeft"
      );

      setTimeout(function () {
        mobileHeaderContent.style.display = "none";
        mobileHeaderContent.classList.remove(
          "animate__animated",
          "animate__fadeOutLeft"
        );
        mobileHeaderContent.style.removeProperty("--animate-duration", "0.8s");
      }, 800);
    });
  });
});

if ($(".popup-youtube").length > 0) {
  $(".popup-youtube").magnificPopup({
    disableOn: 700,
    type: "iframe",
    mainClass: "mfp-fade",
    removalDelay: 160,
    preloader: false,
    fixedContentPos: false,
  });
}

if ($(".image-popup-link").length > 0) {
  $(".image-popup-link").magnificPopup({
    type: "image",
    closeOnContentClick: true,
    mainClass: "mfp-img-mobile",
    image: {
      verticalFit: true,
    },
    gallery: {
      enabled: true,
    },
  });
}

function pageLoader() {
  $("#page-loader").css("display", "flex").fadeIn("fast");
}

function pageLoaderClose() {
  $("#page-loader").css("display", "none").fadeOut("fast");
}

window.onload = function () {
  console.log('onload');
  pageLoaderClose();
};

if ($(".swiper-news-show").length > 0) {
  const swiperNewsShow = new Swiper(".swiper-news-show", {
    loop: true,
    spaceBetween: 15,
    centeredSlides: true,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      type: "bullets",
      clickable: true,
    },
  });
}

if ($(".swiper-project-show").length > 0) {
  const swiperProjectShow = new Swiper(".swiper-project-show", {
    loop: true,
    spaceBetween: 15,
    centeredSlides: true,
  });
}

if ($(".swiper-president-image").length > 0) {
  const swiperPresidentImage = new Swiper(".swiper-president-image", {
    loop: true,
    spaceBetween: 15,
    breakpoints: {
      320: {
        slidesPerView: 1,
      },
      480: {
        slidesPerView: 1,
      },
      640: {
        slidesPerView: 2,
      },
      992: {
        slidesPerView: 3,
      },
    },
  });
}

if ($(".swiper-president-video").length > 0) {
  const swiperPresidentVideo = new Swiper(".swiper-president-video", {
    loop: true,
    spaceBetween: 15,
    breakpoints: {
      320: {
        slidesPerView: 1,
      },
      480: {
        slidesPerView: 1,
      },
      640: {
        slidesPerView: 2,
      },
      992: {
        slidesPerView: 3,
      },
    },
  });
}

$(".sub-menu-title").on("click", function () {
  $(this).next(".secondary-menu").slideToggle();
  $(this).toggleClass("active");
});

document.addEventListener(
  "touchmove",
  function (e) {
    if (e.touches.length === 1) {
      let x = e.touches[0].clientX;
      let y = e.touches[0].clientY;

      if (Math.abs(x - startX) > Math.abs(y - startY)) {
        e.preventDefault();
      }
    }
  },
  { passive: false }
);

let startX, startY;
document.addEventListener("touchstart", function (e) {
  startX = e.touches[0].clientX;
  startY = e.touches[0].clientY;
});

window.addEventListener("load", () => {
  let loader = document.getElementById("page-loader") || false;
  if (loader.length > 0) {
	loader.style.display = "none";
  }
});

$(document).ready(function () {
  $(".hasChild").hover(
    function () {
      let subMenuHeight = $(this).find(".subMenu-column").outerHeight();
      let headerDefaultHeight = $(".header-default").outerHeight();
      let totalHeight = subMenuHeight + headerDefaultHeight;

      $(".header-dynamic").attr("style", "height: " + totalHeight + "px;");
    },
    function () {
      let subMenuHeight = $(this).find(".subMenu-column").outerHeight();
      let headerDefaultHeight = $(".header-default").outerHeight();
      let totalHeight = subMenuHeight + headerDefaultHeight;
      $(".header-dynamic").attr(
        "style",
        "transform: translateY(-100%); height: " + totalHeight + "px;"
      );
    }
  );
});

if ($(".popup-close").length > 0) {
  if (document.querySelector(".popup-close")) {
    let popupClose = document.querySelector(".popup-close");
    let popupId = popupClose.dataset.id;

    let popupShow = localStorage.getItem("popupShow-" + popupId) || false;
    if (!popupShow) document.querySelector(".popup").classList.remove("d-none");

    document.body.style.overflow = "hidden";
    popupClose.addEventListener("click", function () {
      let popup = document.querySelector(".popup");
      popup.style.display = "none";
      document.body.style.overflow = "scroll";
      localStorage.setItem("popupShow-" + popupId, true);
    });
  }
}

/* Global Form */
if ($("#global-form").length > 0) {
  $("#global-form button").click(function () {
    $("#global-form").validate({
      submitHandler: function (form) {
        let $form_action = $("#global-form");
        let $form_action_button = $("#global-form button");

        $.ajax({
          type: $form_action.attr("method"),
          url: $form_action.attr("action"),
          data: $form_action.serialize(),
          dataType: "JSON",
          beforeSend: function () {
            NProgress.start();
            $form_action_button.text(theme.strings.general.loading);
            $form_action_button.prop("disabled", true);
          },
          success: function (result) {
            if (result.success) {
              swal({
                title: theme.strings.general.success.title,
                text: result.success,
                type: "success",
                html: true,
                dangerMode: false,
                closeOnClickOutside: false,
                confirmButtonText: theme.strings.general.close,
              });

              NProgress.done();
              if (result.reset !== false) {
                $form_action.get(0).reset();
              }
              $form_action_button.text($form_action_button.attr("data-button"));
              $form_action_button.prop("disabled", false);
            } else if (result.url) {
              window.parent.location.href = result.url;
            } else {
              swal({
                title: theme.strings.general.error.title,
                text: result.error,
                type: "error",
                html: true,
                dangerMode: false,
                closeOnClickOutside: false,
                confirmButtonText: theme.strings.general.close,
              });
            }
          },
          complete: function () {
            NProgress.done();
            $form_action_button.text($form_action_button.attr("data-button"));
            $form_action_button.prop("disabled", false);
          },
        });
      },
      errorPlacement: function (error, element) {
        $(element).parents(".input-group").parent().append(error);
      },
    });

    $(".required").each(function () {
      $(this).rules("add", {
        required: true,
      });
    });
  });
}

/* Popup */
theme.PopupAgreement = function (value) {
  let popup_name = "#agreementPopup";

  $.ajax({
    type: "POST",
    url: $("base").attr("href") + "/callback",
    data: { action: "agreement-popup", value: value },
    dataType: "JSON",
    beforeSend: function () {
      NProgress.start();
    },
    success: function (result) {
      if (result.success) {
        NProgress.done();

        if ($(popup_name).length > 0) {
          $.magnificPopup.open({
            items: {
              src: popup_name,
            },
            type: "inline",
            preloader: false,
          });

          $(popup_name).find("h4").html(result.success.name);
          $(popup_name).find(".content").html(result.success.description);
        }
      } else {
        swal({
          title: theme.strings.general.error.title,
          text: result.error,
          type: "error",
          html: true,
          dangerMode: false,
          closeOnClickOutside: false,
          confirmButtonText: theme.strings.general.close,
        });
      }
    },
    complete: function () {
      NProgress.done();
    },
  });
};

if ($("#popupContent").length > 0) {
  let popupParam = "";
  if ($(".popup-web").length > 0) {
    popupParam = "popup-web";
  } else if ($(".popup-mobil").length > 0) {
    popupParam = "popup-mobil";
  }

  setTimeout(function () {
    $.magnificPopup.open({
      items: {
        src: "#popupContent",
      },
      type: "inline",
      mainClass: popupParam,
      preloader: false,
    });
  }, 5000);
}

/* Google Map */
if ($("#map").length > 0) {
  function initialize() {
    var element = $("#map");
    var styles = [
      {
        stylers: [{ hue: "#686868" }, { saturation: -100 }, { lightness: -40 }],
      },
    ];

    var zoom = element.data("map-zoom");
    var myLatLng = new google.maps.LatLng(
      element.data("map-lat"),
      element.data("map-lng")
    );
    var styledMap = new google.maps.StyledMapType(styles, {
      name: "Styled Map",
    });
    var mapOptions = {
      zoom: zoom,
      scrollwheel: false,
      center: myLatLng,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    };

    var map = new google.maps.Map(document.getElementById("map"), mapOptions);
    var image = element.data("icon-path");

    var marker = new google.maps.Marker({
      position: myLatLng,
      map: map,
      icon: image,
    });
  }
  google.maps.event.addDomListener(window, "load", initialize);
}
function openItemNew(item, icon) {
  var status = document.getElementById(item).style.display;
  if (status == "none") {
    document.getElementById(item).style.display = "block";
    document.getElementById(icon).classList.remove("fa-bars");
    document.getElementById(icon).classList.add("fa-times");
  } else {
    document.getElementById(item).style.display = "none";
    document.getElementById(icon).classList.add("fa-bars");
    document.getElementById(icon).classList.remove("fa-times");
  }
}
