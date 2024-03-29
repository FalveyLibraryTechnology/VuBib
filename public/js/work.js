var ur = "";
$.fn.textWidth = function(text, font) {
                    if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
                    $.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
                    return $.fn.textWidth.fakeEl.width();
};

// el.closest(sel) polyfill (IE8+)
// https://developer.mozilla.org/en-US/docs/Web/API/Element/closest
if (window.Element && !Element.prototype.closest) {
  Element.prototype.closest =
    function(s) {
      var matches = (this.document || this.ownerDocument).querySelectorAll(s),
          i,
          el = this;
      do {
        i = matches.length;
        while (--i >= 0 && matches.item(i) !== el) {};
      } while ((i < 0) && (el = el.parentElement));
      return el;
    };
}

//Publisher autocomplete and location fetch
const newPubRowAC = new Autocomplete({ minInputLength: 1 });
function bindPublisherAutocomplete() {
  //Publisher autocomplete
  setupACS(
    ".pub-name-acs.acs-unset",
    function ajaxData(input) {
      return { autofor: "publisher", term: input.value };
    },
    function ajaxSuccess(data, callback, input) {
      if(!data.length){
        callback([{ text: "no matches", _disabled: true }]);
      } else {
        // TODO: Add new
        callback($.map(data, function (item) {
          return {
            text: item.name,
            id:    item.id
          }
        }));
      }
    }
  ).forEach(function bindLocationSelect(cell) {
    // Add new publisher if necessary
    cell.addEventListener("acs-set", function() {
      if ($("#Publisher .acs-editing").length === 0) {
        $("#pub_add").click();
      }
    }, false);

    cell.classList.remove("acs-unset");
    var row = cell.closest("tr");
    row.querySelector(".acs-clear").addEventListener("click", function (e) {
      $(".pub-locations", row).html("");
      $("input", row).val("");
    });

    var input = cell.querySelector(".acs-input");
    //publisher enable/disable fields
    $(".pub-locations", row).prop("disabled", !input.value);
    // Populate locations event listener
    input.addEventListener("ac-select", function newPublisherRowSelect(e) {
      $.ajax({
        method: 'get',
        url: ur + workURL,
        data: { autofor: "publisher_loc", term: e.detail.id },
        dataType: "json",
        cache: false,
        success: function(data) {
          const $locationsSel = $(".pub-locations", row);
          $locationsSel.html('');
          if (data.publocs.length === 0) {
            $locationsSel.html('<option id="-1">none available</option>');
            $locationsSel.attr("disabled", true);
            return false;
          }
          $locationsSel.append('<option value="-1">Select a location</option>');
          for (var i = 0; i < data.publocs.length; i++) {
            const val = data.publocs[i];
            $locationsSel.append('<option id="' + val.id + '" value="' + val.id + '">' + val.label + '</option>');
            $('[name="publoc_id[]"]', row).eq(i).val(val.id);
            $locationsSel.attr("disabled", false);
          }
        },
        error: function() {
          $(".pub-locations", row).html('<option id="-1">none available</option>');
          $(".pub-locations", row).attr("disabled", true);
        }
      });
    });
  });
}

//add new publisher
function addNewPublisher(context,workURL,lnk) {
    //var lnk = $('.addNewPubLink', context);
    if (lnk == $('.addNewItemPubLink', context)) {
        $('.addNewPubLink', context).remove();
    }
    var typedText = lnk.closest('tr').find('#pubName').val();
    lnk.closest('tr').find('#pubName').val("");

    $('#addPublisherLookup').modal('show');
    $('#newpublisher').val(typedText);
    $('#addNewPub').unbind('click').on('click', function(e) {
        $.ajax({
            method: 'post',
            url: ur + workURL,
            data: {
                addAction: 'addNewPublisher',
                pubName: $('#newpublisher').val(),
                pubLocation: $('#addpublisherloc').val()
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (Object.keys(data.newPublisher).length > 0) {
                        $('#newpublisher').val('');
                        $('#addpublisherloc').val('');
                        $(".add_new_pub_close").click();
                        lnk.closest('tr').find('#pubId', context).val(data.newPublisher.pub_id);
                        lnk.closest('tr').find('#pubName', context).val(data.newPublisher.pub_name);
                        //Resizing text field to make selected publisher visible
                        var pbLen = lnk.closest('tr').find('#pubName', context).textWidth(data.newPublisher.pub_name) + 35;
                        lnk.closest('tr').find('#pubName', context).css('width',pbLen + 'px');
                        if(typeof(data.newPublisher.pubLoc_id) != "undefined" && data.newPublisher.pubLoc_id !== null) {
                            lnk.closest('tr').find('#pubLocation', context).prop("disabled", false);
                            lnk.closest('tr').find('.pub-locations', context).append('<option id="' + data.newPublisher.pubLoc_id +
                                                                            '" value="' + data.newPublisher.pubLoc_id + '" selected="selected">' +
                                                                             data.newPublisher.pub_loc + '</option>');
                            lnk.closest('tr').find('#publoc_id', context).eq(0).val(data.newPublisher.pubLoc_id);

                            //Setting select to auto width to make selected publisher location visible
                            lnk.closest('tr').find('.pub-locations', context).css('width', 'auto');
                        }
                        lnk.closest('tr').find('#pubName', context).nextAll().remove();
                }
            },
            error: function() {
                $(".option_results", context).append('<p>None available</p>');
            }
        });
        return false;
    });
}

// New Agent Modal
var lastInput = document;
function showNewAgentModal(e) {
    e.preventDefault();
    var modalEl = document.getElementById("add-new-agent");
    if (!modalEl) {
        return false;
    }
    $(modalEl).modal("show");
    var acs = $(lastInput).parent(".agent-acs");
    $(acs).find(".acs-clear").click();
    $(acs).find(".acs-input").val("");
    return false;
}
// submit new agent modal
$(document).ready(function bindAgentModalForm() {
    $("#new-agent-submit").on("click", function submitNewAgentForm(e) {
        e.preventDefault();
        var form = document.getElementById("new-agent-form");
        var formData = new FormData(form);
        var postData = Object.assign(
            { "submitt": "Save" },
            Object.fromEntries(formData.entries())
        );
        $.post(form.getAttribute("action"), postData)
            .done(function(e) {
                $("#agent-modal__error").addClass("hidden");
                $("#agent-modal__success").removeClass("hidden");
                setTimeout(function() {
                    $("#add-new-agent").modal("hide");
                }, 3000);
            })
            .fail(function(e) {
                $("#agent-modal__success").addClass("hidden");
                $("#agent-modal__error").removeClass("hidden");
            });
        return false;
    });
    $("#new-agent-cancel").on("click", function cancelNewAgentForm(e) {
        e.preventDefault();
        $("#add-new-agent").modal("hide");
        return false;
    });
});

//Agent Autocomplete
function bindAgentAutocomplete() {
  //Publisher autocomplete
  setupACS(
    ".agent-acs.acs-unset",
    function ajaxData(input) {
      return { autofor: "agent", term: input.value };
    },
    function ajaxSuccess(data, callback, input) {
      lastInput = input;
      let results = [{ text: "no matches", _disabled: true }];
      if (data.length > 0) {
        results = $.map(data, function (item) {
          item.text = item.lname + ", " + item.fname;
          item.value = item.lname;
          return item;
        });
      }

      results.push({ text: "<i>Add New Agent</i>", value: input.value, _action: "showNewAgentModal" });
      input.addEventListener("ac-select", function clickAddNewAgent(e) {
        if (
          typeof e.detail._action != "undefined" &&
          e.detail._action == "showNewAgentModal"
        ) {
          showNewAgentModal(e);
        }
      }, { once: true });

      callback(results);
    }
  ).forEach(function bindLocationSelect(acs) {
    acs.classList.remove("acs-unset");
    var input = acs.querySelector(".acs-input");
    var row = acs.closest("tr");
    // Populate locations event listener
    input.addEventListener("ac-select", function newPublisherRowSelect(e) {
      row.querySelector(".agent-fname").innerHTML = e.detail.fname;
      row.querySelector(".agent-alternate_name").innerHTML = e.detail.alternate_name;
      row.querySelector(".agent-organization_name").innerHTML = e.detail.organization_name;
    });
    // Clear row on change
    function clearRow() {
      row.querySelector(".agent-fname").innerHTML = "";
      row.querySelector(".agent-alternate_name").innerHTML = "";
      row.querySelector(".agent-organization_name").innerHTML = "";
    }
    row.querySelector(".acs-change").addEventListener("click", clearRow, { passive: true });
    row.querySelector(".acs-clear").addEventListener("click", clearRow, { passive: true });
  });
}

//add new agent
function addNewAgent(context,workURL,lnk) {
    //var lnk = $('.addNewAgLink', context);
    if (lnk == $('.addNewItemAgLink', context)) {
        $('.addNewAgLink', context).remove();
    }
    var typedText = lnk.closest('tr').find('#agent_LastName').val();
    lnk.closest('tr').find('#agent_LastName').val("");

    $('#addAgentLookup').modal('show');
    $('#newagentlastname').val(typedText);
    $('#addNewAg').unbind('click').on('click', function(e) {
        $.ajax({
            method: 'post',
            url: ur + workURL,
            data: {
                addAction: 'addNewAgent',
                agFName: $('#newagentfirstname').val(),
                agLName: $('#newagentlastname').val(),
                agAltName: $('#newagentaltname').val(),
                agOrgName: $('#newagentorgname').val(),
                agEmail: $('#newagentemail').val()
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                if (Object.keys(data.newAgent).length > 0) {
                        $('#newagentfirstname').val('');
                        $('#newagentlastname').val('');
                        $('#newagentaltname').val('');
                        $('#newagentorgname').val('');
                        $('#newagentemail').val('');
                        $(".add_new_ag_close").click();

                        lnk.closest('tr').find('#agentId', context).val(data.newAgent.ag_id);
                        lnk.closest('tr').find('#agent_LastName', context).val(data.newAgent.ag_lname);

                        //Resizing text field to make selected agent last name visible
                        var agent_ln = lnk.closest('tr').find('#agent_LastName', context).textWidth(data.newAgent.ag_lname) + 25; //data.newAgent.ag_lname.length + 5;
                        var agent_fn = lnk.closest('tr').find('#agent_FirstName', context).textWidth(data.newAgent.ag_fname) + 25; //data.newAgent.ag_fname.length + 5;
                        $('#agent_LastName', context).css('width', agent_ln + 'px');
                        if (data.newAgent.ag_fname != '') {
                            lnk.closest('tr').find("#agent_FirstName", context).prop("disabled", false);
                            lnk.closest('tr').find("#agent_FirstName", context).val(data.newAgent.ag_fname);
                            //Resizing text field to make selected agent first name visible
                            lnk.closest('tr').find('#agent_FirstName', context).css('width', agent_fn + 'px');
                        }
                        if (data.newAgent.ag_altname != '') {
                            lnk.closest('tr').find("#agent_AlternateName", context).prop("disabled", false);
                            lnk.closest('tr').find("#agent_AlternateName", context).val(data.newAgent.ag_altname);
                        }
                        if (data.newAgent.ag_orgname != '') {
                            lnk.closest('tr').find("#agent_OrganizationName", context).prop("disabled", false);
                            lnk.closest('tr').find("#agent_OrganizationName", context).val(data.newAgent.ag_orgname);
                        }

                        lnk.closest('tr').find('#agent_LastName', context).nextAll().remove();
                }
            },
            error: function() {
                //$(".option_results", context).append('<p>None available</p>');
            }
        });
        return false;
    });
}

//WorkType Autocomplete
function bindWorkTypeAttributes(context, workURL, sattrURL) {
    //$('#Citation *').not('.ig').remove();
    //$(".content_box a").not(".button")
    var worktype_Id = $('#work_type').val();
    $.ajax({
        method: 'post',
        url: ur + workURL,
        data: {
            worktype_Id: worktype_Id
        },
        dataType: "json",
        cache: false,
        success: function(data) {
            $("#Citation").html("");
            $.each(data.worktype_attribute, function(key, val) {
                const valEl = document.getElementById("work-citation-" + val.id);
                const set = valEl
                    ? (key) => valEl.getAttribute(key)
                    : (key) => "";
                // append input control at end of form
                if (val.type == 'Text') {
                    $('<div class="form-group required">' +
                          '<label class="control-label col-sm-2">' + val.field + ':</label>' +
                          '<div class="col-sm-10">' +
                              '<input class="form-control" type="text" name="wkatid,' + val.id + '" id="' + val.field + '" value="' + set("value") + '" size="50"/>' +
                          '</div>' +
                      '</div>').appendTo("#Citation");
                }
                if (val.type == 'Textarea') {
                    $('<div class="form-group required">' +
                          '<label class="control-label col-sm-2">' + val.field + ':</label>' +
                          '<div class="col-sm-10">' +
                              '<textarea class="form-control" name="wkatid,' + val.id + '" id="' + val.field + '" rows="4"/>' + set("value") + "</textarea>" +
                          '</div>' +
                      '</div>').appendTo("#Citation");
                }
                if (val.type == 'RadioButton') {
                    $('<div class="form-group required">' +
                          '<label class="control-label col-sm-2">' + val.field + ':</label>' +
                          '<div class="col-sm-10">' +
                              '<input class="form-control" type="radio" name="wkatid,' + val.id + '" value="true" /> True<br>' +
                              '<input class="form-control" type="radio" name="wkatid,' + val.id + '" value="false" /> False<br>' +
                          '</div>' +
                      '</div>').appendTo("#Citation");
                }
                if (val.type == 'Select') { // Autocomplete
                    $('<div class="form-group required">' +
                        '<div class="col-sm-2 text-right">' +
                          '<label class="control-label" for="' + val.field + ':' + val.id + '">' + val.field + ':</label><br/>' +
                          '<a class="addNewAttrOptionLink"' +
                          ' data-attribute-id="'+ val.field + ':' + val.id +'"' +
                          ' href="#' + val.field + ':' + val.id +'">Add New</a>' +
                        '</div>' +
                        '<div class="col-sm-10">' +
                          '<div class="attribute-ac">' +
                            '<input class="acs-hidden" type="hidden" name="wkatid,' + val.id + '" value="' + set("value") + '"/>' +
                            '<input class="form-control acs-input" id="' + val.field + ':' + val.id + '" type="text" value="' + set("title") + '" placeholder="Type to search"/>' +
                          '</div>' +
                        '</div>' +
                      '</div>').appendTo("#Citation");
                }
            });

            setupACS(
              ".attribute-ac",
              input => ({ option: input.value, "attribute_Id": input.id }),
              acsStandardResults("attribute_options", item => ({ text: item.title, id: item.id })),
              "POST"
            );
            $(".addNewAttrOptionLink").on("click",function() {
              addNewOption(this.dataset.attributeId);
              return false;
            });
            /*
            // Autocomplete test
            const AC = new Autocomplete({ minInputLength: 1 });
            const citationInputs = $(".option-ac");
            for (let i = 0; i < citationInputs.length; i++) {
              const input = citationInputs[i];
              AC(input, function periodicalAC(query, callback) {
                $.ajax({
                  method: "POST",
                  url: URL + "/Work/get_work_details",
                  data: { option: query, "attribute_Id": input.id }
                })
                  .done(function(data) {
                    callback(data.attribute_options.map(x => x.title));
                    if (data.attribute_options.length === 0) {
                      input.classList.add("ac-no-results");
                    } else {
                      input.classList.remove("ac-no-results");
                    }
                  });
              });
            }
            $(".addNewAttrOptionLink").on("click",function() {
              addNewOption(this.dataset.attributeId);
              return false;
            });

            $(".optionLookupBtn").on('click', function(e) {
                // show Modal
                var lookupBtn = this;
                attr_option_lookup = $(lookupBtn).prev();
                $("#optionsLookup").modal('show');
                $('#optionsLookup').on('shown.bs.modal', function() {
                    $('#lookupOption').val('');
                    $(".option_results").html('');
                    $('#lookupOption').focus();
                    $('.option_search').unbind('click').on('click', function(e) {
                        //var attribute_Id = $(lookupBtn).prev().attr('id');
                        var attribute_Id = attr_option_lookup.attr('id');
                        var option = $('#lookupOption').val();
                        $.ajax({
                            method: 'post',
                            //url: 'http://localhost<?= $this->url('get_work_details') ?>',
                            url: ur + workURL,
                            data: {
                                option: option,
                                attribute_Id: attribute_Id
                            },
                            dataType: "json",
                            cache: false,
                            success: function(data) {
                                if(data.attribute_options.length > 0) {
                                    $(".option_results", context).html('');
                                    $(".option_results", context).append('<p>Search Results</p>');
                                    $.each(data.attribute_options, function(key, val) {
                                        $(".option_results", context).append('<p><a name="' + val.id + '" href="' + val.title + '" class="link_options">' + val.title + '</a></p>');
                                    })
                                }
                                if($.trim($('.option_results').html()).length > 0) {
                                    $( ".link_options:last" ).after('<li><a class="addNewAttrOptionLink" ' +
                                                                'style="text-decoration: underline; color:blue" ' +
                                                                'data-value="'+ option +'"' +
                                                                'data-ele=""' + '>Add New</a></li>' );
                                } else {
                                    $(".option_results", context).append('<p>No Search Results</p>' +
                                                                        '<li><a class="addNewAttrOptionLink" ' +
                                                                        'style="text-decoration: underline; color:blue" ' +
                                                                        'data-value="'+ option +'"' +
                                                                        'data-ele=""' + '>Add New</a></li>');
                                }
                                $('.addNewAttrOptionLink').on('click',function(){
                                    var lnk = $(".option_results", context);
                                    addNewOption(context,workURL,lnk,attribute_Id,option);
                                    return false;
                                });
                            },
                            error: function() {
                                $(".option_results", context).append('<p>No search results</p>');
                            }
                        });
                        $(context).off('click', '.link_options');
                        $(context).on("click", ".link_options", function(e) {
                            var linkval = $(this).attr('href');
                            var optId = $(this).attr('name');
                            attr_option_lookup.val(linkval);
                            attr_option_lookup.attr('name', attr_option_lookup.attr('name') + 'optid,' + $(this).attr('name'));
                            $.ajax({
                                method: 'post',
                                url: ur + workURL,
                                data: {
                                    attribute_Id: attribute_Id,
                                    option_id: optId,
                                    subattr: 'chk_subattr'
                                },
                                dataType: "json",
                                cache: false,
                                success: function(data) {
                                    if(Object.keys(data.subattr).length > 0) {
                                        var attr_id = data.subattr.attr_id;
                                        //$(lookupBtn).after('&nbsp;<button type="button" class="btn btn-link openBtn" data-url="' + sattrURL + '?wkat_id=' + attr_id + '&id=' + optId + '&action=edit_sattr_vals" >Edit '
                                        //+ data.subattr.subattr + '</button>');
                                        $(lookupBtn).after('&nbsp;&nbsp;<a href="' +
                                        sattrURL + '?wkat_id=' + attr_id + '&id=' + optId + '&action=edit_sattr_vals" target="subattrTab">' +
                                        'Edit ' + data.subattr.subattr + '</a>');
                                    }
                                },
                                error: function() {
                                }
                            });
                            $('#lookupOption').val('');
                            $(".option_results", context).html('');
                            $('.option_lookup_close').trigger('click');
                            return false;
                        });
                    });
                });
                return false;
            });
            */
        },
        error: function(data) {
            $("#Citation", context).html('<p>No Options</p>');
        }
    });

    return false;
}

//add new option
function addNewOption(attributeId) {
    const attributeType = attributeId.split(":")[0];
    $("#new-option-label").html(attributeType);
    $('#addAttributeOption').modal('show');

    $('#addNewOpt').unbind('click').on('click', function(e) {
        $.ajax({
            method: 'post',
            url: URL + "/Work/get_work_details",
            data: {
                addAction: 'addNewOption',
                attrId: attributeId,
                attrOption: $('#newoption').val(),
                //attrType: $('#addoptiontype').val()
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                $("#addAttributeSuccess").removeClass("hidden");
                $('[id="' + attributeId + '"]').val(data.newOption.opt_title);
                setTimeout(_ => {
                    $('#addAttributeOption').modal("hide");
                    $("#addAttributeSuccess").addClass("hidden");
                    $('[id="' + attributeId + '"]').focus();
                }, 2000);
            },
            error: function(e) {
                console.error(e);
            }
        });
        return false;
    });
}

//Add classification hierarchy

_select = '';
function bindClassification(select, workURL, for_str) {
    console.log("bindClassification");

    // Add new row if necessary
    let needNew = !!$("#fl_add").length;
    $('[data-source-col="0"] select').each((i, select) => {
      if (!needNew) {
        return;
      }
      if (!$(select).val()) {
        needNew = false;
      }
    });
    if (needNew) {
      $("#fl_add").click();
    }

    var $targetRow = $(select).closest("tr");
    var selectedId = $(select).val();
    var dataCol = for_str + "-col";

    // Remove later selects
    var changedIndex = $(select).closest("td").data(for_str + "-col");
    $targetRow.find("td").each(function(_, cell) {
        var $cell = $(cell);
        if ($cell.data(dataCol) > changedIndex) {
            $cell.remove();
        }
    });

    // Set width
    // $(select).css(
    //     "width",
    //     ($(select).find("option:selected").text().length + 5) + 'ch'
    // );

    if (selectedId) {
        $.ajax({
            url: workURL,
            data: {
                autofor: "folder",
                term: "children:" + selectedId
            },
            dataType: "json",
            success: function(folder_children) {
                // Make new select
                if (folder_children.length > 0) {
                    var $newSelect = $('<select class="form-control select_' + for_str + '_fl">');
                    $newSelect.attr("name", "select_" + for_str + "_fl[]");
                    $newSelect.append('<option value=""></option>');
                    for (var folder of folder_children) {
                        $newSelect.append(
                            '<option value="' + folder.id + '">' + folder.text_fr + '</option>'
                        );
                    }
                    $newSelect.on('change', function() {
                        bindClassification(this, workURL, for_str);
                        return false;
                    });
                    $newCell = $("<td>")
                        .attr("class", for_str + "_fl_col")
                        .attr("data-" + dataCol, changedIndex + 1)
                        .append($newSelect);
                    $targetRow.append($newCell);
                }
            },
            error: function(data) {
                console.error(data);
            }
        });
    }
    return false;
}

//Parent lookup
function bindParentWorkAutocomplete() {
  console.log("bindParentWorkAutocomplete");
  setupACS(
    "#parent-work-lookup",
    function parentWorkACData(input) {
      return { autofor: "work", term: input.value }
    },
    function parentWorkACSuccess(prnt_lookup, callback, input) {
      input.classList.remove("ac-no-results");
      callback(
        prnt_lookup.map(x => ({
          id: x.id,
          text: x.title,
          sub: x.type
        }))
      );
    },
  );
}

/*
function bindParentWork(context,workURL)
{
  console.log("bindParentWork");
    $('.pr_work_results').html('');
    $('#parent_title',context).focus();

   $('.parent_lookup_search',context).off('click').on('click', function(e) {
       var lookup_title = $('#parent_title',context).val();
       if (lookup_title != "") {
           $.ajax({
               url: ur + workURL,
               data: {
                  autofor: "work",
                  term: lookup_title
               },
               dataType: "json",
               success: function(data) {
                   if ((data.prnt_lookup.length) > 0) {
                       $('#parent_title').val('');
                       var prworks_result_table = '<table style="font-size:10pt; border-collapse: separate; border-spacing: 10px;" id="prworks_result_table">' +
                                                  '<tr><th>Work Title</th><th>Type</th></tr>';
                       $.each(data.prnt_lookup, function (key, val) {
                           prworks_result_table += '<tr><input type="hidden" name="parent_work_id" id="parent_work_id" value="">' +
                                                   '<td><a name="' + val.id + '" href="" class="prwork_link" value="' + val.title + '">' +
                                                   val.title + '</a></td><td>' + val.type + '</td></tr>';
                       });
                       prworks_result_table += '</table>';
                       $('.pr_work_results').append(prworks_result_table);
                   } else {
                         $('#parent_title',context).val('');
                         $('.pr_work_results').append('<p>No records found</p>');
                   }

               },
               error: function(data) {
                  alert("No results");
               }
           });
           //$(context).off('click', '.prwork_link');
           $(context).on("click", ".prwork_link", function(e) {
               var pr_linkval = $(this).attr('name');
               var pr_labelval = $(this).attr('value');
               $('.pr_work_div', context).text(pr_labelval);
               $('#pr_work_lookup_id',context).val(pr_linkval);
               $('.pr_work_div', context).append('&nbsp;&nbsp;<button type="button" class="btn btn-secondary btn-xs parent_Chng_Btn" name="parent_changeBtn" id="parent_changeBtn" ' +
                                                 'data-toggle="modal" data-target="#parentLookup_modal">Change</button>')
               $('.pr_work_div', context).append('&nbsp;&nbsp;<button type="button" class="btn btn-secondary btn-xs parent_Rmv_Btn" name="parent_removeBtn" ' +
                                                  'id="parent_removeBtn">Remove</button>')
               $('.pr_work_results').html('');
               $('.option_lookup_close').trigger('click');
                   return false;
           });
       }
       return false;
   });
   return false;
}*/

//Add merge buutton on Publisher > Merge
function addMergeButton(context, for_str) {
    if(for_str === "pub_merge") {
        if (($('#mrg_src_id', context).val().length != 0) && ($('#mrg_dest_id', context).val().length != 0)) {
            $('#submit_clear', context).before('<button type="submit" class="btn btn-default" name="submitt" id="submit_save" value="Save">Merge</button>');
            return false;
        }
    } else if (for_str === "opt_merge") {
        $('#submit_clear', context).before('<button type="submit" class="btn btn-default" name="submitt" id="submit_save" value="Merge_Options">Merge</button>');
        return false;
    }
}

//For Publisher merge
function _new_findPublisherLocAjax(publisherID, callback) {
  $.ajax({
    url: workURL,
    data: { autofor: "publisher_loc", term: publisherID },
    dataType: "json",
    cache: false,
    success: function(data) {
      if (data.publocs.length === 0) {
        el.innerHTML = "No locations found. Check the database.";
        return;
      }
      callback(data.publocs);
    }
  });
  // Bind buttons
}
function _new_findSourcePublisherLoc(el, publisherID) {
  el.innerHTML = "Loading...";
  _new_findPublisherLocAjax(publisherID, function createSourceSelection(data) {
    // Make list with buttons
    let html = "<p>Select an action for each location:</p>";
    for (let i = 0; i < data.length; i++) {
      const loc = data[i];
      html += '<div class="location-label">' + loc.location +
        '<label class="location-source-control"><input type="radio" name="src_loc[' + loc.id + ']" value="merge"> Merge</label>' +
        '<label class="location-source-control"><input type="radio" name="src_loc[' + loc.id + ']" value="move"> Move</label>' +
        '<small>' + loc.works + " works</small></div>";
    }
    el.innerHTML = html;
  });
}
function _new_findDestinationPublisherLoc(el, publisherID) {
  el.innerHTML = "Loading...";
  _new_findPublisherLocAjax(publisherID, function createDestinationSelection(data) {
    // Make list with buttons
    let html = "<p>Select which location to merge locations into:</p>";
    for (let i = 0; i < data.length; i++) {
      const loc = data[i];
      html += '<label class="location-label">' +
        '<input type="radio"] name="dest_loc[' + loc.id + ']" value="merge"/> ' +
        loc.location + '<small>' + loc.works + " works</small></label>";
    }
    el.innerHTML = html;
  });
}
function bindFindPublisher(workURL) {
  setupACS(
    ".publisher-acs",
    function ajaxData(input) {
      return { autofor: "publisher", term: input.value };
    },
    function(data, callback, input) {
      let srcVal = document.getElementById("mrg_src_id").value ?? null;
      callback(
        data.map(x => ({
          id: x.id,
          text: x.name,
          sub: x.work_count + " works",
          _disabled: srcVal == x.id
        }))
      );
    }
  );
  // Source publisher locations
  const srcInput = document.getElementById("find_src_pub");
  const srcLocationEl = document.getElementById("src_locations");
  srcInput.addEventListener("ac-select", function findSourceLocs(e) {
    console.log(srcInput);
    _new_findSourcePublisherLoc(srcLocationEl, e.detail.id);
  }, false);
  document.querySelectorAll("#acs_publisher_merge_src .acs-change, #acs_publisher_merge_src .acs-clear")
    .forEach(function(btn) {
      btn.addEventListener("click", function clearSourceLocations() {
        srcLocationEl.innerHTML = "";
      });
    });
  // Destination publisher locations
  const destInput = document.getElementById("find_dest_pub");
  const destLocationEl = document.getElementById("dest_locations");
  destInput.addEventListener("ac-select", function findDestLocs(e) {
    _new_findDestinationPublisherLoc(destLocationEl, e.detail.id);
  }, false);
  document.querySelectorAll("#acs_publisher_merge_dest .acs-change, #acs_publisher_merge_dest .acs-clear")
    .forEach(function(btn) {
      btn.addEventListener("click", function clearDestLocations() {
        destLocationEl.innerHTML = "";
      });
    });

  // Bind Merge Button
  document.getElementById("merge_pub_form").addEventListener("submit", function submitPublisherMerge(event) {
    const missingErrorEL = document.getElementById("mergeMissingError");
    const identicalErrorEL = document.getElementById("mergePubError");
    const pubSelErrorEL = document.getElementById("mergePubSelError");

    missingErrorEL.style.display = "none";
    identicalErrorEL.style.display = "none";
    pubSelErrorEL.style.display = "none";

    const srcHiddenEl = document.getElementById("mrg_src_id");
    const destHiddenEl = document.getElementById("mrg_dest_id");

    if (srcHiddenEl.value === "" || destHiddenEl.value === "") {
      event.preventDefault();
      missingErrorEL.style.display = "block";
      return false;
    }
    if (srcHiddenEl.value === destHiddenEl.value) {
      event.preventDefault();
      identicalErrorEL.style.display = "block";
      return false;
    }
    const sourceForm = document.getElementById("src_locations");
    const numOfCheckedRadios = sourceForm.querySelectorAll(":checked").length;
    const numOfSourceLocations = sourceForm.querySelectorAll(".location-label").length;
    if (numOfCheckedRadios < numOfSourceLocations) {
      event.preventDefault();
      pubSelErrorEL.style.display = "block";
      return false;
    }
    return true;
  }, false);

  // Bind Clear Button
  document.getElementById("submit_clear").addEventListener("click", function clearPublisherMerge() {
    document.querySelectorAll(".acs-clear").forEach(function(btn) { btn.click(); });
  }, false);
}
function findPublisher(workURL, for_str) {
    var find_pub = $('#find_' + for_str + '_pub').val();
    $.ajax({
        method: 'post',
        url: ur + workURL,
        data: {
            pub_name: find_pub
        },
        dataType: "json",
        cache: false,
        success: function (data) {
            if ((data.pub_row.length) > 0) {
                $('#find_' + for_str + '_pub').val('');
                $('#' + for_str + '_find_outer_div').after('<div class="form-group" id="' + for_str + '_select_div">');
                var result_table = '<table id="' + for_str + '_result_table" style="font-size:10pt; border-collapse: separate; border-spacing: 10px;">' +
                                '<tr><th>Select</th><th>Publisher Name</th><th>Works</th></tr>';
                $.each(data.pub_row, function (key, val) {
                    if (for_str === "dest") {
                        if (("" + val.id) !== ($('#mrg_src_id').val())) {
                            result_table += '<tr class="' + for_str + '_pub_row"><td><div class="radio">' +
                                            '<label>' +
                                            '<input class="form-control" type="radio" id="' + for_str + '_select" name="' + for_str + '_select" value="' + val.id + '">' +
                                            '</label>' +
                                            '</div></td>' +
                                            '<td class="' + for_str + '_pub_name">' + val.name + '</td>' +
                                            '<td>' + val.works + '</td></tr>';
                        }
                    } else {
                        result_table += '<tr class="' + for_str + '_pub_row"><td><div class="radio">' +
                                        '<label>' +
                                        '<input class="form-control" type="radio" id="' + for_str + '_select" name="' + for_str + '_select" value="' + val.id + '">' +
                                        '</label>' +
                                        '</div></td>' +
                                        '<td class="' + for_str + '_pub_name">' + val.name + '</td>' +
                                        '<td>' + val.works + '</td></tr>';
                    }
                });
                result_table += '</table>';
                $('#' + for_str + '_select_div').append(result_table);
                $('#' + for_str + '_result_table').append('<button type="button" class="btn btn-default" name="btn_select_' + for_str + '" ' +
                                                              'id="btn_select_' + for_str + '">Select</button>');
              } else {
                $('#find_' + for_str + '_pub').val('');
              }
        },
        error: function (data) {}
    });
    return false;
}

//Publisher merge - get publisher locations
function findPublisherLoc(context, workURL, for_str) {
    var pub = $('input[name="' + for_str + '_select"]:checked').val();
    var sel_pubName = $('input[name="' + for_str + '_select"]:checked').closest('tr').children('td.' + for_str + '_pub_name').text();
    var pub_result_table = for_str + '_result_table';
    if (pub !== null) {
        if(for_str === "src") {
                $("#find_dest_pub").prop("disabled", false);
                $("#dest_pub_find_bt").prop("disabled", false);
        }
        $('#mrg_' + for_str + '_id').val(pub);
        $('#find_' + for_str + '_pub').val(sel_pubName);
        $.ajax({
            method: 'post',
            url: ur + workURL,
            data: {
                publisher_Id_locs: pub
            },
            dataType: "json",
            cache: false,
            success: function (data) {
                if ((data.pub_locs.length) > 0) {
                    $('#' + for_str + '_result_table').html('');
                    $('#' + for_str + '_find_outer_div').after('<div class="form-group" id="' + for_str + '_loc_select_div">');
                    if(for_str === "src") {
                        console.log(for_str);
                        var loc_result_table = '<table id="' + for_str + '_loc_result_table" style="font-size:10pt; border-collapse: separate; border-spacing: 10px;">' +
                                                '<tr><th>Move</th><th>Merge</th><th>Location</th><th>Works</th></tr>';
                        $.each(data.pub_locs, function (key, val) {
                            loc_result_table += '<tr class="src_loc_row"><td>' +
                                                '<input class="form-control" type="radio" id="src_loc_mv_select" name="src_loc[' + val.id + ']" value="move">' + '</td>' +
                                                '<td>'+
                                                '<input class="form-control" type="radio" id="src_loc_mrg_select" name="src_loc[' + val.id + ']" value="merge">' + '</td>' +
                                                '<td>' + val.location + '</td><td>' + val.works + '</td></tr>';
                        });
                    } else {
                        console.log("else " + for_str);
                        var loc_result_table = '<table id="' + for_str + '_loc_result_table" style="font-size:10pt; border-collapse: separate; border-spacing: 10px;">' +
                                                '<tr><th>Merge</th><th>Location</th><th>Works</th></tr>';
                        $.each(data.pub_locs, function (key, val) {
                            loc_result_table += '<tr class="' + for_str + '_loc_row"><td>' +
                                                '<input class="form-control" type="radio" id="' + for_str + '_loc_mrg_select" name="' + for_str + '_loc[' + val.id + ']" value="merge">' + '</td>' +
                                                '<td>' + val.location + '</td><td>' + val.works + '</td></tr>';
                        });
                    }
                    pub_result_table += '</table>';
                    $('#' + for_str + '_loc_select_div').append(loc_result_table);
                } else {
                            $('#' + for_str + '_result_table').html('');
                }
            },
            error: function (data) {}
        });
        if (for_str === "dest") {
            addMergeButton(context, "pub_merge");
        }
    }
    return false;
}

//For Attribute Options merge
function findAttrOptions(workURL, for_str, wkat_id) {
    var find_opt = $('#find_' + for_str + '_opt').val();
    if(for_str === "dest") {
        var sel_opts = [];
        var sel_ids = $('#src_opts_hidden').val();
        sel_opts = sel_ids.split(',');
    }
    $.ajax({
        method: 'post',
        url: ur + workURL,
        data: {
            opt_name: find_opt,
            wkat_id: wkat_id
        },
        dataType: "json",
        cache: false,
        success: function (data) {
            if ((data.opt_row.length) > 0) {
                $('#find_' + for_str + '_opt').val('');
                $('#' + for_str + '_find_outer_div').after('<div class="form-group" id="' + for_str + '_select_div">');
                var result_table = '<table id="' + for_str + '_result_table" style="font-size:10pt; border-collapse: separate; border-spacing: 10px;">' +
                                   '<tr><th>Select</th><th>Option Title</th><th>Works</th></tr>';
                $.each(data.opt_row, function (key, val) {
                    if (for_str === "dest") {
                        if ($.inArray("" + val.id, sel_opts) == -1) {
                            result_table += '<tr class="dest_opt_row"><td><div class="radio">' +
                                            '<label>' +
                                            '<input class="form-control" type="radio" id="dest_select" name="dest_select" value="' + val.id + '">' +
                                            '</label>' +
                                            '</div></td>' +
                                            '<td class="dest_opt_name">' + val.title + '</td>' +
                                            '<td>' + val.works + '</td></tr>';
                        }
                    } else {
                        result_table += '<tr class="src_opt_row"><td><div class="checkbox">' +
                                        '<label>' +
                                        '<input class="form-control" type="checkbox" id="src_select" name="src_select[]" value="' + val.id + '">' +
                                        '</label>' +
                                        '</div></td>' +
                                        '<td class="src_opt_name">' + val.title + '</td>' +
                                        '<td>' + val.works + '</td></tr>';
                    }
                });
                result_table += '</table>';
                $('#' + for_str + '_select_div').append(result_table);
                $('#' + for_str + '_result_table').append('<button type="button" class="btn btn-default" name="btn_select_' + for_str + '" ' +
                                                          'id="btn_select_' + for_str + '">Select</button>');
              } else {
                $('#find_' + for_str + '_opt').val('');
              }
        },
        error: function (data) {}
    });
    return false;
}

/*
function mergeClassification(that, context, workURL, for_str)
{
    var to_add_row = $(that).closest("tr");
    if ($(that).val() == "") {
        to_add_row.find('.' + for_str + '_fl_col').eq(0).nextAll('.' + for_str + '_fl_col').remove();
        to_add_row.find('.' + for_str + '_fl_col').eq(0).val('');
        //return false;
    } else {
        fl_changed = $(that).val();

        var no_of_fl_parent = to_add_row.find('.select_' + for_str + '_fl').length;
        for (var i = 0; i < no_of_fl_parent; i++) {
            if (to_add_row.find('.select_' + for_str + '_fl').eq(i).val() === fl_changed) {
                change_idx = i;
                folder_Id = to_add_row.find('.select_' + for_str + '_fl').eq(i).val();
                break;
            }
        }
        to_add_row.find('.' + for_str + '_fl_col').eq(change_idx).nextAll('.' + for_str + '_fl_col').remove();

        no_of_fl_parent = to_add_row.find('.select_' + for_str + '_fl').length;

        $.ajax({
            method: 'post',
            //url: 'http://localhost<?= $this->url('get_work_details') ?>',
            url: ur + workURL,
            data: {
                folder_Id: folder_Id
            },
            dataType: "json",
            cache: false,
            success: function(data) {
                to_add_row.find('.' + for_str + '_fl_col').eq(no_of_fl_parent - 1).after('<td class="' + for_str + '_fl_col" ' +
                                                                                              'name="' + for_str + '_fl_col" ' +
                                                                                              'id="' + for_str + '_fl_col" ' +
                                                                                              '/>');

                _select = $('<select class="form-control select_' + for_str + '_fl select2" name="select_' + for_str + '_fl[]">');
                to_append = $('<option value=""></option>');
                $.each(data.folder_children, function(key, val) {
                    to_append += '<option value="' + val.id + '">' + val.text_fr + '</option>';
                });
                _select.append($('<option />'));
                _select.append(to_append);

                to_add_row.find('.' + for_str + '_fl_col').eq(no_of_fl_parent).append(_select);
                to_add_row.find('.' + for_str + '_fl_col').eq(no_of_fl_parent).append('</select>');

                _select.on('change', function() {
                    bindClassification(this, document, workURL, for_str);
                    return false;
                });
            },
            error: function(data) {
                $("#Classification", context).html('<p>No Options</p>');
            }
        });
        //return false;
    }
    return false;
}
*/