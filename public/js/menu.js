var active = false;
var lastmenu;

function getId(id)
{
    if (document.getElementById){
        return document.getElementById(id);
    } else if (document.all) {
        return document.all.id;
    } else {
        return document.id;
    }
}

function hover(elem, id)
{
    elem.style.backgroundColor='#6699CC';
    if (active) {
        expand(id);
    }
}

function unhover(elem)
{
    elem.style.backgroundColor='#EEEEEE';
}

function expand(id)
{
    elem = getId(id);
    if (elem.style.visibility == 'visible') {
        active = false;
        elem.style.visibility = 'hidden';
        elemImg = getId(id + 'img');
        elemImg.src = 'images/right.gif';
    } else {
        active = true;
        hideAll();
        elem.style.visibility = 'visible';
        elemImg = getId(id + 'img');
        elemImg.src = 'images/down.gif';
        lastmenu = elem;
    }
}

function hideAll()
{
    if (lastmenu) {
        lastmenu.style.visibility = 'hidden';
    }
}

$(document).ready(function menuReady() {
$('.items a').on('click', function() {
  var $this = $(this),
      $bc = $('<div class="item"></div>');

  $this.parents('li').each(function(n, li) {
      var $a = $(li).children('a').clone();
      $bc.prepend(' / ', $a);
  });
    $('.breadcrumb').html( $bc.prepend('<a href="#home">Home</a>') );
    return false;
})
});

// Autocomplete selector
const ascAC = new Autocomplete({ limit: false });
function acsStandardData(key) {
  return function(input) {
    let data = {};
    data[key] = input.value;
    return data;
  };
}
function acsStandardResults(key, mapFunc) {
  return function(data, callback, input) {
    if (data[key].length === 0) {
      input.classList.add("ac-no-results");
      callback([]);
    } else {
      input.classList.remove("ac-no-results");
      callback(data[key].slice(0, 10).map(mapFunc));
    }
  };
}
function setupACS(selector, ajaxData, ajaxSuccess, method = "GET") {
  let elements = document.querySelectorAll(selector);
  elements.forEach(function setupACSel(el) {
    // Run setup
    const input = el.querySelector(".acs-input");
    if (!input) {
      console.error(input);
      return;
    }
    // Bind autocomplete
    ascAC(input, function ascAutocomplete(_, callback) {
      $.ajax({
        method: method,
        url: workURL,
        data: ajaxData(input)
      }).done(function(data) {
        try {
          ajaxSuccess(data, callback, input);
        } catch (e) {
          console.error("Invalid JSON returned", e);
        }
      }).fail(function(e) {
        console.error("ACS Request Error", e);
        callback(false);
      });
    });
    // Add UI
    let titleEl, changeBtn, clearBtn;
    if (!el.querySelector(".acs-title")) {
      // Make new elements
      titleEl = document.createElement("span");
      titleEl.className = "acs-title";
      const btnContainer = document.createElement("div");
      btnContainer.className = "acs-btn-container";
      changeBtn = document.createElement("button");
      changeBtn.className = "acs-change btn btn-default btn-xs";
      changeBtn.innerHTML = "Change";
      clearBtn = document.createElement("button");
      clearBtn.className = "acs-clear btn btn-default btn-xs";
      clearBtn.innerHTML = "Clear";
      el.appendChild(titleEl);
      el.appendChild(btnContainer);
      btnContainer.appendChild(changeBtn);
      btnContainer.appendChild(clearBtn);
    } else {
      // Select UI elements
      titleEl = el.querySelector(".acs-title");
      changeBtn = el.querySelector(".acs-change");
      clearBtn = el.querySelector(".acs-clear");
    }
    // Add event listeners
    const hiddenEl = el.querySelector(".acs-hidden");
    input.addEventListener("ac-select", function bindParentHidden(e) {
      titleEl.innerHTML = e.detail.value || e.detail.text;
      el.className = el.className.replace("acs-editing", "acs-set");
      el.dispatchEvent(new Event("acs-set"));
      if (hiddenEl) {
        hiddenEl.value = e.detail.id;
      }
    }, false);
    // Change button
    changeBtn.addEventListener("click", function changeParentWork(e) {
      e.preventDefault();
      input.value = titleEl.innerHTML;
      el.className = el.className.replace("acs-set", "acs-editing");
      input.select();
      if (hiddenEl) {
        hiddenEl.value = "";
      }
    }, false);
    // Clear button
    clearBtn.addEventListener("click", function removeParentWork(e) {
      e.preventDefault();
      input.value = "";
      el.className = el.className.replace("acs-set", "acs-editing");
      if (hiddenEl) {
        hiddenEl.value = "";
      }
    }, false);
    // Set initial state
    if (typeof input.value === "undefined" || input.value === "") {
      el.className = el.className.replace("acs-set", "");
      el.className += " acs-editing";
    } else {
      el.className += " acs-set";
      el.className = el.className.replace("acs-editing", "");
      titleEl.innerHTML = input.value;
    }
  });
  return elements;
}
