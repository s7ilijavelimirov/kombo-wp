jQuery(document).ready(function ($) {
  var noDateLabel =
    typeof komboCartI18n !== "undefined" && komboCartI18n.noDate
      ? komboCartI18n.noDate
      : "Nije odabran datum";

  $(document).on("click", ".remove", function (e) {
    e.preventDefault();

    var $thisbutton = $(this);
    var $product = $thisbutton.closest(".product-wrapper");
    var cart_item_key = $thisbutton.data("cart_item_key");

    $.ajax({
      type: "POST",
      url: wc_cart_params.ajax_url,
      data: {
        action: "woocommerce_remove_from_cart",
        cart_item_key: cart_item_key,
        security: wc_cart_params.nonce,
      },
      success: function (response) {
        if (!response || !response.fragments) {
          window.location.reload();
          return;
        }
        window.location.reload();
      },
      error: function () {
        window.location.reload();
      },
    });
  });

  $("form.woocommerce-cart-form").on("submit", function (e) {
    e.preventDefault();

    var $form = $(this);

    $form.block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });

    $.ajax({
      type: "POST",
      url: wc_cart_params.wc_ajax_url
        .toString()
        .replace("%%endpoint%%", "update_cart"),
      data: $form.serialize(),
      success: function (response) {
        $form.unblock();

        if (response && response.fragments) {
          $.each(response.fragments, function (key, value) {
            $(key).replaceWith(value);
          });
        }

        if (!$(".woocommerce-message").length) {
          $(".woocommerce-cart-form").before(
            '<div class="woocommerce-message" role="alert">Korpa je ažurirana.</div>'
          );
        }

        $(".summary-wrapper .order-total.shipping p").html("0 RSD");

        $(document.body).trigger("wc_fragment_refresh");
        $(document.body).trigger("updated_cart_totals");
      },
    });
  });

  $(document).on("click", 'button[name="update_cart"]', function (e) {
    e.preventDefault();

    var $form = $("form.woocommerce-cart-form");

    if (!$form.find('input[name="update_cart"]').length) {
      $form.append('<input type="hidden" name="update_cart" value="true">');
    }

    $form.block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });

    $.ajax({
      type: "POST",
      url: $form.attr("action"),
      data: $form.serialize(),
      dataType: "html",
      complete: function () {
        $.ajax({
          type: "POST",
          url: wc_cart_params.ajax_url,
          data: {
            action: "update_cart_prices",
            security: $form
              .find('input[name="woocommerce-cart-nonce"]')
              .val(),
          },
          complete: function () {
            window.location.reload();
          },
        });
      },
    });
  });

  $(document).on("change", ".qty", function () {
    $('button[name="update_cart"]').prop("disabled", false);
    $("form.woocommerce-cart-form").addClass("needs-update");
  });

  $(document.body).on("updated_cart_totals", function () {
    $(".summary-wrapper .order-total.shipping p").html("0 RSD");

    setTimeout(function () {
      $(".woocommerce-message").fadeOut(400, function () {
        $(this).remove();
      });
    }, 3000);
  });

  function getMonthIndex(monthName) {
    var months = [
      "januar",
      "februar",
      "mart",
      "april",
      "maj",
      "jun",
      "jul",
      "avgust",
      "septembar",
      "oktobar",
      "novembar",
      "decembar",
    ];
    return months.indexOf(monthName.toLowerCase());
  }

  function getMonthName(index) {
    var months = [
      "januar",
      "februar",
      "mart",
      "april",
      "maj",
      "jun",
      "jul",
      "avgust",
      "septembar",
      "oktobar",
      "novembar",
      "decembar",
    ];
    return months[index];
  }

  /** Prikaz kao u PHP (npr. "Mart"). */
  function formatMonthWord(index) {
    var w = getMonthName(index);
    return w.charAt(0).toUpperCase() + w.slice(1);
  }

  function parseDateDisplay(displayText) {
    var dates = [];
    var currentYear = new Date().getFullYear();

    displayText.split(";").forEach(function (monthGroup) {
      monthGroup = monthGroup.trim();
      if (!monthGroup) return;

      var lastDotIndex = monthGroup.lastIndexOf(".");
      if (lastDotIndex === -1) return;

      var daysStr = monthGroup.substring(0, lastDotIndex);
      var monthName = monthGroup.substring(lastDotIndex + 1).trim();
      var monthIndex = getMonthIndex(monthName);

      if (monthIndex !== -1) {
        var days = daysStr
          .split(",")
          .map(function (d) {
            return parseInt(d.replace(/\./g, "").trim(), 10);
          })
          .filter(function (d) {
            return !isNaN(d);
          });

        days.forEach(function (day) {
          var date = new Date(currentYear, monthIndex, day);
          dates.push($.datepicker.formatDate("dd-mm-yy", date));
        });
      }
    });

    return dates;
  }

  function parseSingleDateDisplay(text) {
    var m = text.trim().match(/^(\d+)\.\s*([^\s.;]+)/i);
    if (!m) return [];
    var day = parseInt(m[1], 10);
    var monthIndex = getMonthIndex(m[2]);
    if (monthIndex === -1) return [];
    var y = new Date().getFullYear();
    return [
      $.datepicker.formatDate("dd-mm-yy", new Date(y, monthIndex, day)),
    ];
  }

  function formatDatesGroupByMonth(dates) {
    if (!dates.length) return "";

    var grouped = {};
    dates.forEach(function (dateStr) {
      var parts = dateStr.split("-").map(Number);
      var day = parts[0];
      var month = parts[1];
      var year = parts[2];
      var monthKey = month + "-" + year;
      if (!grouped[monthKey]) {
        grouped[monthKey] = [];
      }
      grouped[monthKey].push(day);
    });

    return Object.entries(grouped)
      .sort()
      .map(function (entry) {
        var monthKey = entry[0];
        var days = entry[1];
        var month = monthKey.split("-").map(Number)[0];
        return (
          days.sort(function (a, b) {
            return a - b;
          }).join("., ") +
          ". " +
          formatMonthWord(month - 1)
        );
      })
      .join(" ; ");
  }

  function formatLineLabel(isDaily, dates) {
    if (!dates || !dates.length) {
      return noDateLabel;
    }
    if (isDaily) {
      return formatDatesGroupByMonth(dates);
    }
    var parts = dates[0].split("-").map(Number);
    var d = parts[0];
    var m = parts[1];
    return d + ". " + formatMonthWord(m - 1);
  }

  function updateDateDisplayUi($wrapper, isDaily, dates) {
    var $span = $wrapper.find(".date-display");
    $span.text(formatLineLabel(isDaily, dates));
    if (dates && dates.length) {
      $span.addClass("has-date");
    } else {
      $span.removeClass("has-date");
    }
  }

  $(".product-wrapper").each(function () {
    var $wrapper = $(this);
    var $datepickerInput = $wrapper.find(".cart-datepicker");
    if (!$datepickerInput.length) return;

    var $dateDisplay = $wrapper.find(".date-display");
    var cartItemKey = $wrapper.find(".remove").data("cart_item_key");
    var $updateButton = $wrapper.find('button[name="update_cart"]');
    var pkg = ($wrapper.data("package-type") || "").toString();
    var isDaily = pkg === "dnevni";
    var isWeekdaysOnly = pkg === "nedeljni5" || pkg === "mesecni20";

    var selectedDates = [];

    if ($dateDisplay.hasClass("has-date")) {
      var t = $dateDisplay.text().trim();
      if (t && t !== noDateLabel) {
        selectedDates = isDaily ? parseDateDisplay(t) : parseSingleDateDisplay(t);
      }
    }

    function updateCartDates(dates) {
      $.ajax({
        url: wc_cart_params.ajax_url,
        type: "POST",
        data: {
          action: "update_cart_delivery_date",
          cart_item_key: cartItemKey,
          new_date: dates,
          security: $('input[name="woocommerce-cart-nonce"]').val(),
        },
        success: function (response) {
          if (response.success) {
            updateDateDisplayUi($wrapper, isDaily, dates);
            $updateButton.prop("disabled", false);
            $("form.woocommerce-cart-form").addClass("needs-update");
            if (!$(".woocommerce-info").length) {
              $(".woocommerce-cart-form").before(
                '<div class="woocommerce-info">Izabrali ste nove datume. Kliknite na "Ažuriraj korpu" da sačuvate promene.</div>'
              );
            }
          }
        },
      });
    }

    $datepickerInput.datepicker({
      dateFormat: "dd-mm-yy",
      minDate: 1,
      changeMonth: false,
      changeYear: false,
      dayNamesMin: ["Ned", "Pon", "Uto", "Sre", "Čet", "Pet", "Sub"],
      beforeShow: function (input, inst) {
        if (isWeekdaysOnly) {
          inst.dpDiv.addClass("weekdays-only");
        } else {
          inst.dpDiv.removeClass("weekdays-only");
        }
      },
      beforeShowDay: function (date) {
        var day = date.getDay();
        var dateStr = $.datepicker.formatDate("dd-mm-yy", date);

        if (day === 0) return [false, ""];
        if (isWeekdaysOnly && day === 6) return [false, ""];

        return [
          true,
          selectedDates.indexOf(dateStr) !== -1 ? "ui-state-highlight" : "",
        ];
      },
      onSelect: function (dateText) {
        if (isDaily) {
          var dateIndex = selectedDates.indexOf(dateText);
          if (dateIndex > -1) {
            selectedDates.splice(dateIndex, 1);
          } else {
            selectedDates.push(dateText);
          }
          selectedDates.sort();
        } else {
          selectedDates = [dateText];
        }

        updateCartDates(selectedDates);
        $updateButton.prop("disabled", false);
      },
    });

    function openPicker(e) {
      if (e.type === "keydown" && e.key !== "Enter" && e.key !== " ") {
        return;
      }
      e.preventDefault();
      e.stopPropagation();
      $datepickerInput.datepicker("show");
    }

    $wrapper.find(".date").on("click", openPicker);
    $wrapper.find(".date").on("keydown", openPicker);
  });
});
