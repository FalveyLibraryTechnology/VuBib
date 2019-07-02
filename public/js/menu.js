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
const ascAC = new Autocomplete();
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
function setupACS(selector, ajaxData, ajaxSuccess) {
  document.querySelectorAll(selector).forEach(function setupACSel(el) {
    const input = el.querySelector(".acs-input");
    ascAC(input, function periodicalAC(query, callback) {
      $.ajax({
        method: "POST",
        url: workURL,
        data: ajaxData(input)
      }).done(function(json) {
        try {
          const data = JSON.parse(json);
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
    const titleEl = document.createElement("span");
    titleEl.className = "acs-title";
    const btnContainer = document.createElement("div");
    btnContainer.className = "acs-btn-container";
    const changeBtn = document.createElement("span");
    changeBtn.className = "acs-change btn btn-default btn-xs";
    changeBtn.innerHTML = "Change";
    const clearBtn = document.createElement("span");
    clearBtn.className = "acs-clear btn btn-default btn-xs";
    clearBtn.innerHTML = "Clear";
    el.appendChild(titleEl);
    el.appendChild(btnContainer);
    btnContainer.appendChild(changeBtn);
    btnContainer.appendChild(clearBtn);
    // Add event listeners
    const hiddenEl = el.querySelector(".acs-hidden");
    input.addEventListener("ac-select", function bindParentHidden(e) {
      hiddenEl.value = e.detail.id;
      titleEl.innerHTML = e.detail.text;
      el.className = el.className.replace("acs-editing", "acs-set");
    }, false);
    changeBtn.addEventListener("click", function changeParentWork(e) {
      input.value = titleEl.innerHTML;
      hiddenEl.value = "";
      el.className = el.className.replace("acs-set", "acs-editing");
      input.select();
    }, false);
    clearBtn.addEventListener("click", function removeParentWork(e) {
      input.value = "";
      hiddenEl.value = "";
      el.className = el.className.replace("acs-set", "acs-editing");
    }, false);
    // Set initial state
    if (typeof input.value === "undefined" || input.value === "") {
      el.className += " acs-editing";
    } else {
      el.className += " acs-set";
      titleEl.innerHTML = input.value;
    }
  });
}
