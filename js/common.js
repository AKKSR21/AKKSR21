// メニューバーの色を変化させる
function changeColor() {
if (document.getElementById("eyecatch") != null) {
  let scrollY = window.pageYOffset;
  let body = document.body;

  let eyecatch = document.getElementById('eyecatch');
  let nav = document.getElementById('globalnav');

  let top = eyecatch.getBoundingClientRect().top; // ウィンドウ上からの要素の位置
  let bottom = eyecatch.getBoundingClientRect().bottom; // ウィンドウ上からの要素の位置

  let area = bottom - top;

  if( scrollY < area ) {
    nav.classList.add('colorchange');
  } else {
    nav.classList.remove('colorchange');
  }
 }
}

window.addEventListener('scroll', changeColor);

// ハンバーガーメニュー実装
const checkbox = document.getElementById('menubtn');
const navmenu = document.getElementById('navmenu');

function openMenu() {
  if (navmenu.className == '') {
    navmenu.classList.add('checked');
    checkbox.style.color = 'black';
  } else {
    navmenu.classList.remove('checked');
    checkbox.style.color = '';
  }
}
checkbox.addEventListener('click', openMenu);

jQuery(function() {
  jQuery(document).on('click', function(e) {
      if(!jQuery(e.target).is('#navmenu, #menubtn') && $(window).width() < 750 && navmenu.className == 'checked') {
          navmenu.classList.remove('checked');
          checkbox.style.color = '';
      }
    });
});

jQuery(function() {
  // クリック時のぬるっとスクロール
  jQuery('a[href*="#"]').click(function() {
    let hash = this.hash;
    if (hash === undefined) return;

    let target = (hash === "#" || hash === "") ? jQuery('html') : jQuery(hash);
    if (!target.length) return;

    let windowWidth = jQuery(window).width();
    let offset = windowWidth < 750 ? 60 : 80;
    let position = target.offset().top - offset;

    let speed = 500;
    jQuery('html, body').animate({ scrollTop: position }, speed);
    return false;
  });

  // 読み込み時にハッシュがある場合もぬるっとスクロール
  jQuery(window).on('load', function() {
    let hash = window.location.hash;
    if (!hash) return;

    let target = (hash === "#" || hash === "") ? jQuery('html') : jQuery(hash);
    if (!target.length) return;

    let windowWidth = jQuery(window).width();
    let offset = windowWidth < 750 ? 60 : 80;
    let position = target.offset().top - offset;

    let speed = 500;
    // setTimeout でブラウザのデフォルトスクロール後に上書き
    setTimeout(function() {
      jQuery('html, body').animate({ scrollTop: position }, speed);
    }, 10);
  });
});

// メニューの中のリンクをクリックしたらメニューを閉じる
jQuery("#navmenu a[href]").on("click", function() {
    jQuery('#menubtn').trigger('click');
  });