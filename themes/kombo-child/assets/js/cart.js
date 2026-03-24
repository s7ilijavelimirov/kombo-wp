jQuery(document).ready(function ($) {
  // Handler za brisanje proizvoda
  $(document).on("click", ".remove", function (e) {
    e.preventDefault();

    var $thisbutton = $(this);
    var $product = $thisbutton.closest(".product-wrapper");
    var product_id = $thisbutton.data("product_id");
    var cart_item_key = $thisbutton.data("cart_item_key");

    $.ajax({
      type: "POST",
      url: wc_cart_params.ajax_url,
      data: {
        action: "woocommerce_remove_from_cart",
        cart_item_key: cart_item_key,
        security: wc_cart_params.nonce,
      },
      beforeSend: function () {
        $product.block({
          message: null,
          overlayCSS: {
            opacity: 0.6,
          },
        });
      },
      success: function (response) {
        if (!response || !response.fragments) {
          window.location.reload();
          return;
        }

        // Umesto animacije i ostalih akcija, samo refreshujemo stranicu
        window.location.reload();
      },
      error: function () {
        window.location.reload();
      },
    });
  });
});
jQuery(document).ready(function ($) {
  $(".product-wrapper").each(function () {
    const $wrapper = $(this);
    const $datepickerInput = $wrapper.find(".cart-datepicker");
    const $dateDisplay = $wrapper.find(".date");
    const cartItemKey = $wrapper.find(".remove").data("cart_item_key");
    const $updateButton = $wrapper.find('button[name="update_cart"]');

    // Dobavljamo tip paketa iz imena proizvoda
    const productName = $wrapper.find(".product-name").text().toLowerCase();
    const isDaily = productName.includes("dnevni");
    const isWeekdaysOnly =
      productName.includes("nedeljni 5") ||
      productName.includes("mesecni20") ||
      productName.includes("mesečni 20") ||
      productName.includes("nedeljni5");

    // Niz za čuvanje selektovanih datuma
    let selectedDates = [];

    // Inicijalno učitavanje datuma
    if (isDaily) {
      const currentDateText = $dateDisplay.text().trim();
      if (currentDateText && currentDateText !== "Nije odabran datum") {
        selectedDates = parseDateDisplay(currentDateText);
      }
    }

    // Inicijalizacija datepickera
    $datepickerInput.datepicker({
      dateFormat: "dd-mm-yy",
      minDate: 1,
      changeMonth: false,
      changeYear: false,
      dayNamesMin: ["Ned", "Pon", "Uto", "Sre", "Čet", "Pet", "Sub"],
      beforeShowDay: function (date) {
        const day = date.getDay();
        const dateStr = $.datepicker.formatDate("dd-mm-yy", date);

        if (day === 0) return [false, ""];
        if (isWeekdaysOnly && day === 6) return [false, ""];

        return [
          true,
          selectedDates.includes(dateStr) ? "ui-state-highlight" : "",
        ];
      },
      onSelect: function (dateText) {
        if (isDaily) {
          const dateIndex = selectedDates.indexOf(dateText);
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
            // Omogućimo dugme za ažuriranje
            $updateButton.prop("disabled", false);

            // Dodamo klasu koja označava da korpa treba ažuriranje
            $("form.woocommerce-cart-form").addClass("needs-update");

            // Prikazujemo poruku korisniku da treba da klikne na "Ažuriraj korpu"
            if (!$(".woocommerce-info").length) {
              $(".woocommerce-cart-form").before(
                '<div class="woocommerce-info">Izabrali ste nove datume. Kliknite na "Ažuriraj korpu" da sačuvate promene.</div>'
              );
            }
          }
        },
      });
    }
    $("form.woocommerce-cart-form").on("submit", function (e) {
      e.preventDefault();

      const $form = $(this);

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

          // Osvežavamo fragmente i totale
          if (response && response.fragments) {
            $.each(response.fragments, function (key, value) {
              $(key).replaceWith(value);
            });
          }

          // Prikazujemo poruku o uspešnom ažuriranju
          if (!$(".woocommerce-message").length) {
            $(".woocommerce-cart-form").before(
              '<div class="woocommerce-message" role="alert">Korpa je ažurirana.</div>'
            );
          }

          // Postavljamo dostavu na 0
          $(".summary-wrapper .order-total.shipping p").html("0 RSD");

          // Trigger za WooCommerce događaje
          $(document.body).trigger("wc_fragment_refresh");
          $(document.body).trigger("updated_cart_totals");
        },
      });
    });

    $(document).on("click", 'button[name="update_cart"]', function (e) {
      e.preventDefault();

      const $form = $("form.woocommerce-cart-form");

      // Dodajemo hidden input za update_cart
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
          // Prvo osvežimo cene
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
              // Na kraju refreshujemo stranicu
              window.location.reload();
            },
          });
        },
      });
    });

    // Dodajemo i handler za promenu količine
    $(document).on("change", ".qty", function () {
      $('button[name="update_cart"]').prop("disabled", false);
      $("form.woocommerce-cart-form").addClass("needs-update");
    });

    // Dodajemo observer za promene u korpi
    $(document.body).on("updated_cart_totals", function () {
      // Forsiramo prikaz nulte vrednosti za dostavu
      $(".summary-wrapper .order-total.shipping p").html("0 RSD");

      // Uklanjamo poruku nakon određenog vremena
      setTimeout(function () {
        $(".woocommerce-message").fadeOut(400, function () {
          $(this).remove();
        });
      }, 3000);
    });

    function wc_price(price) {
      return price.toLocaleString("sr-RS", {
        style: "currency",
        currency: "RSD",
      });
    }

    // Event handler za kalendarsku ikonicu
    $dateDisplay.on("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      $datepickerInput.datepicker("show");
    });

    function parseDateDisplay(displayText) {
      const dates = [];
      const currentYear = new Date().getFullYear();

      displayText.split(";").forEach((monthGroup) => {
        monthGroup = monthGroup.trim();
        if (!monthGroup) return;

        const lastDotIndex = monthGroup.lastIndexOf(".");
        if (lastDotIndex === -1) return;

        const daysStr = monthGroup.substring(0, lastDotIndex);
        const monthName = monthGroup.substring(lastDotIndex + 1).trim();
        const monthIndex = getMonthIndex(monthName);

        if (monthIndex !== -1) {
          const days = daysStr
            .split(",")
            .map((d) => parseInt(d.replace(/\./g, "").trim()))
            .filter((d) => !isNaN(d));

          days.forEach((day) => {
            const date = new Date(currentYear, monthIndex, day);
            dates.push($.datepicker.formatDate("dd-mm-yy", date));
          });
        }
      });

      return dates;
    }

    function updateDateDisplay(formattedText) {
      const currentContent = $dateDisplay.html();
      const svgEnd = currentContent.indexOf("</svg>") + 6;
      const svgContent = currentContent.substring(0, svgEnd);
      $dateDisplay.html(svgContent + " " + formattedText);
    }

    function formatDatesGroupByMonth(dates) {
      if (!dates.length) return "";

      const grouped = {};
      dates.forEach((dateStr) => {
        const [day, month, year] = dateStr.split("-").map(Number);
        const monthKey = `${month}-${year}`;
        if (!grouped[monthKey]) {
          grouped[monthKey] = [];
        }
        grouped[monthKey].push(day);
      });

      return Object.entries(grouped)
        .sort()
        .map(([monthKey, days]) => {
          const [month] = monthKey.split("-").map(Number);
          return `${days.sort((a, b) => a - b).join("., ")}. ${getMonthName(
            month - 1
          )}`;
        })
        .join(" ; ");
    }

    function getMonthIndex(monthName) {
      const months = [
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
      const months = [
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
  });
});
