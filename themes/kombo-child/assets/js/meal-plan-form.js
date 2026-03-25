jQuery(document).ready(function ($) {
  function mpTrace() {
    if (typeof meal_plan_vars !== "undefined" && meal_plan_vars.trace) {
      console.log.apply(console, ["[meal-plan]"].concat([].slice.call(arguments)));
    }
  }

  function mpPllLang() {
    return typeof meal_plan_vars !== "undefined" && meal_plan_vars.pll_lang
      ? meal_plan_vars.pll_lang
      : "";
  }

  // Form state
  class MealPlanState {
    constructor() {
      this.menuType = "standard"; // novi property
      this.gender = "";
      this.calories = "";
      this.package = "";
      this.dates = [];
      this.currentStep = "menu-type";
    }
  }

  class MealPlanForm {
    constructor() {
      this.state = new MealPlanState();

      if (!$("#deliveryDate").length) {
        $(".date-selection").html(`
          <div class="calendar-trigger">
            <div class="date-range-wrapper">
              <div class="date-range-display">
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M7.66667 6.33333V1M18.3333 6.33333V1M6.33333 11.6667H19.6667M3.66667 25H22.3333C23.0406 25 23.7189 24.719 24.219 24.219C24.719 23.7189 25 23.0406 25 22.3333V6.33333C25 5.62609 24.719 4.94781 24.219 4.44772C23.7189 3.94762 23.0406 3.66667 22.3333 3.66667H3.66667C2.95942 3.66667 2.28115 3.94762 1.78105 4.44772C1.28095 4.94781 1 5.62609 1 6.33333V22.3333C1 23.0406 1.28095 23.7189 1.78105 24.219C2.28115 24.719 2.95942 25 3.66667 25Z" stroke="#2D2D2D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
               <span class="calendar-label">${mealTranslations.calendar}</span>
              </div>
            </div>
          </div>
          <input type="text" id="deliveryDate" class="datepicker" readonly style="display: none;">
        `);
      }

      // Inicijalizujemo event handler za kalendar
      $(document)
        .off("click", ".calendar-trigger")
        .on("click", ".calendar-trigger", (e) => {
          e.preventDefault();
          e.stopPropagation();
          const $datepicker = $("#deliveryDate");
          if ($datepicker.length) {
            $datepicker.datepicker("show");
          }
        });

      this.init();
    }

    init() {
      // Sakrijemo dugmiće na početku
      $(".form-buttons").hide();
      $(".submit-button").prop("disabled", true);

      this.updateArrowPosition("menu-type");
      this.initializeDatepicker();
      this.bindEvents();
      this.validateForm();
      this.initButtonValidation();
    }
    initButtonValidation() {
      // Validacija je u delegated click handlerima + addToCart (izbegavamo duplirane listenere).
    }

    updateArrowPosition(step) {
      const $arrow = $(".form-arrow");
      const steps = ["menu-type", "gender", "calories", "package", "calendar"];
      const currentStepIndex = steps.indexOf(this.state.currentStep);
      const newStepIndex = steps.indexOf(step);

      if (!$arrow.hasClass("visible")) {
        $arrow.addClass("visible");
      }

      // Dozvoljavamo pomeranje samo unapred ili reset na menu-type
      if (step === "menu-type") {
        $arrow
          .removeClass(
            "step-menu-type step-gender step-calories step-package step-calendar locked"
          )
          .addClass(`step-${step}`);
      } else if (newStepIndex > currentStepIndex) {
        $arrow
          .removeClass("step-menu-type step-gender step-calories step-package step-calendar")
          .addClass(`step-${step}`);

        // Ako je novi korak calendar, dodajemo locked klasu
        if (step === "calendar") {
          $arrow.addClass("locked");
        }
      }

      this.state.currentStep = step;
    }

    validateForm() {
      const isValid =
        this.state.gender &&
        this.state.calories &&
        this.state.package &&
        this.state.dates.length > 0;

      // Dugmići se prikazuju samo kad su SVE vrednosti validne
      $(".form-buttons")[isValid ? "show" : "hide"]();
      $(".submit-button").prop("disabled", !isValid);

      return isValid;
    }

    resetForm() {
      // Reset state (zadržavamo menuType)
      const currentMenuType = this.state.menuType;
      this.state = {
        menuType: currentMenuType,
        gender: "",
        calories: "",
        package: "",
        dates: [],
      };

      // Reset UI
      $(".form-button").removeClass("active");
      $("#selectedGender, #selectedCalories, #selectedPackage").val("");
      $(".calendar-trigger").removeClass("active");
      $(".meal-plan-price").empty();
      $(".meal-plan-price-wrapper").hide();

      // Reset dugmadi
      $(".form-buttons").hide();
      $(".submit-button.add-to-cart")
        .text(mealTranslations.addToCart)
        .prop("disabled", true);
      $(".submit-button.buy-now")
        .text(mealTranslations.buyNow)
        .prop("disabled", true);

      // Reset calories buttons
      $(".calories-buttons .form-button")
        .addClass("calorie-placeholder")
        .removeClass("with-calories")
        .text("Kcal");

      // Reset package prices
      $(".package-price").each(function () {
        const $price = $(this);
        if ($price.closest('[data-package="dnevni"]').length) {
          $price.html("rsd po danu");
        } else {
          $price.html("rsd");
        }
      });

      // Reset date selection ali zadržimo strukturu
      $(".start-date").empty();
      $("#deliveryDate").val("");

      this.updateArrowPosition("gender");
      this.validateForm();
    }

    resetFormAfterMenuTypeChange(menuType) {
      // Reset selections osim menuType
      this.state.gender = "";
      this.state.calories = "";
      this.state.package = "";
      this.state.dates = [];

      // Reset UI
      $(".gender-buttons .form-button").removeClass("active");
      $(".calories-buttons .form-button").removeClass("active");
      $(".package-buttons .form-button").removeClass("active");
      $("#selectedGender, #selectedCalories, #selectedPackage").val("");
      $(".calendar-trigger").removeClass("active");
      $(".meal-plan-price").empty();
      $(".meal-plan-price-wrapper").hide();
      $(".form-buttons").hide();
      $(".submit-button").prop("disabled", true);
      $(".start-date").empty();
      $("#deliveryDate").val("");

      if (menuType === "vege") {
        // Sakri gender buttons (Slim/Fit/Protein Plus)
        $("#gender-buttons").hide();

        // Postavi vege kao gender i prikazi vege calories
        this.state.gender = "vege";
        $("#selectedGender").val("vege");

        // Ažuriraj calories opcije za vege
        this.updateCaloriesOptionsForVege();

        // Pomeri strelicu na calories korak
        this.updateArrowPosition("calories");
      } else {
        // Prikaži gender buttons
        $("#gender-buttons").show();

        // Reset calories buttons na placeholder
        $(".calories-buttons .form-button")
          .addClass("calorie-placeholder")
          .removeClass("with-calories")
          .text("Kcal");

        // Pomeri strelicu na gender korak
        this.updateArrowPosition("gender");
      }

      // Reset package prices
      $(".package-price").each(function () {
        const $price = $(this);
        if ($price.closest('[data-package="dnevni"]').length) {
          $price.html("rsd po danu");
        } else {
          $price.html("rsd");
        }
      });

      this.validateForm();
    }

    updateCaloriesOptionsForVege() {
      const $buttons = $(".calories-buttons .form-button");
      $buttons.css("opacity", "0");

      setTimeout(() => {
        $buttons
          .eq(0)
          .text(`${mealTranslations.small} - 1400 ${mealTranslations.kcal}`)
          .data("calories", 1400);
        $buttons
          .eq(1)
          .text(`${mealTranslations.large} - 1900 ${mealTranslations.kcal}`)
          .data("calories", 1900);
        $buttons.eq(2).hide();

        $buttons
          .addClass("with-calories")
          .removeClass("calorie-placeholder")
          .slice(0, 2)
          .show()
          .css("opacity", "1");
      }, 300);
    }

    checkButtonsVisibility() {
      if (this.state.gender && this.state.calories && this.state.package) {
        $(".form-buttons").show();

        // Proveravamo datume
        const hasDate = this.state.dates && this.state.dates.length > 0;
        console.log("Checking dates in visibility:", hasDate);

        // Enable/disable dugmad i dodaj/ukloni event listenere
        $(".submit-button.add-to-cart, .submit-button.buy-now").each(
          (_, button) => {
            if (!hasDate) {
              // Ako nema datuma, omogući dugme ali dodaj listener za alert
              $(button).prop("disabled", false);
            } else {
              // Ako ima datuma, omogući dugme i ukloni listener
              $(button).prop("disabled", false);
            }
          }
        );

        $(".meal-plan-price-wrapper").show();
        this.updatePrice();
      }
    }
    // Dodajte novi kod ovde
    bindEvents() {
      // Menu Type selection (Standardni / Vege)
      $(".menu-type-buttons .menu-type-btn").on("click", (e) => {
        e.preventDefault();
        const menuType = $(e.currentTarget).data("menu-type");

        $(".menu-type-buttons .menu-type-btn").removeClass("active");
        $(e.currentTarget).addClass("active");

        this.state.menuType = menuType;
        $("#selectedMenuType").val(menuType);

        // Reset forme kada se menja tip menija
        this.resetFormAfterMenuTypeChange(menuType);
      });

      // Gender selection
      $(".gender-buttons .form-button").on("click", (e) => {
        e.preventDefault();
        const selectedGender = $(e.currentTarget).data("gender");

        $(".gender-buttons .form-button").removeClass("active");
        $(e.currentTarget).addClass("active");

        this.state.gender = selectedGender;
        $("#selectedGender").val(selectedGender);

        this.updateCaloriesOptions(selectedGender);
        this.updateArrowPosition("calories");
        this.checkButtonsVisibility();
      });

      // Calories selection
      $(".calories-buttons .form-button").on("click", (e) => {
        e.preventDefault();
        const calories = $(e.currentTarget).data("calories");

        $(".calories-buttons .form-button").removeClass("active");
        $(e.currentTarget).addClass("active");

        this.state.calories = calories;
        $("#selectedCalories").val(calories);

        this.updateAllPrices();
        this.updateArrowPosition("package");
        this.checkButtonsVisibility();
      });

      // Package selection
      $(".package-buttons .form-button").on("click", (e) => {
        e.preventDefault();
        const selectedPackage = $(e.currentTarget).data("package");

        $(".package-buttons .form-button").removeClass("active");
        $(e.currentTarget).addClass("active");

        this.state.package = selectedPackage;
        $("#selectedPackage").val(selectedPackage);

        this.state.dates = [];
        $(".start-date").empty();
        $(".calendar-trigger").addClass("active");

        this.initializeDatepicker();
        this.updateArrowPosition("calendar");

        if (this.state.gender && this.state.calories && this.state.package) {
          $(".form-buttons").show();
          $(".submit-button").prop("disabled", false);
          this.updatePrice();
          $(".meal-plan-price-wrapper").show();
        }
        this.checkButtonsVisibility();
      });

      // Direktno hvatanje klika na dugmad, ignorišući disabled stanje
      const cartButton = document.querySelector(".submit-button.add-to-cart");
      const buyButton = document.querySelector(".submit-button.buy-now");

      if (cartButton) {
        cartButton.addEventListener(
          "mousedown",
          (e) => {
            console.log("Cart button clicked - checking dates");
            if (!this.state.dates || this.state.dates.length === 0) {
              e.preventDefault();
              e.stopPropagation();
              alert("Molimo popunite sva polja");
              return false;
            }
          },
          true
        );
      }

      if (buyButton) {
        buyButton.addEventListener(
          "mousedown",
          (e) => {
            console.log("Buy button clicked - checking dates");
            if (!this.state.dates || this.state.dates.length === 0) {
              e.preventDefault();
              e.stopPropagation();
              alert("Molimo popunite sva polja");
              return false;
            }
          },
          true
        );
      }

      $("#mealPlanForm").on("submit", (e) => {
        e.preventDefault();
      });

      $(document).on("click", ".submit-button.add-to-cart", (e) => {
        e.preventDefault();
        mpTrace("click_add_to_cart");
        this.submitMealPlanToWooCommerce(false);
      });

      $(document).on("click", ".submit-button.buy-now", (e) => {
        e.preventDefault();
        mpTrace("click_buy_now");
        this.submitMealPlanToWooCommerce(true);
      });
    }

    updateCaloriesOptions(gender) {
      const caloriesMap = {
        slim: { small: 1300, large: 1600 },
        fit: { small: 1600, large: 1900 },
        protein: { small: 2000, large: 2600 },
      };

      const options = caloriesMap[gender];
      if (!options) return;

      const $buttons = $(".calories-buttons .form-button");
      $buttons.css("opacity", "0");

      setTimeout(() => {
        $buttons
          .eq(0)
          .text(
            `${mealTranslations.small} - ${options.small} ${mealTranslations.kcal}`
          )
          .data("calories", options.small);
        $buttons
          .eq(1)
          .text(
            `${mealTranslations.large} - ${options.large} ${mealTranslations.kcal}`
          )
          .data("calories", options.large);
        $buttons.eq(2).hide();

        $buttons
          .addClass("with-calories")
          .slice(0, 2)
          .show()
          .css("opacity", "1");
      }, 300);

      // Reset calories selection
      $buttons.removeClass("active");
      this.state.calories = "";
      $("#selectedCalories").val("");
    }

    updateAllPrices() {
      if (!this.state.gender || !this.state.calories) return;

      const packages = [
        "dnevni",
        "nedeljni5",
        "nedeljni6",
        "mesecni20",
        "mesecni24",
      ];

      packages.forEach((packageType) => {
        $.ajax({
          url: meal_plan_vars.ajax_url,
          type: "POST",
          data: {
            action: "get_meal_plan_price",
            nonce: meal_plan_vars.nonce,
            pll_lang: mpPllLang(),
            menu_type: this.state.menuType,
            gender: this.state.gender,
            calories: this.state.calories,
            package: packageType,
          },
          success: (response) => {
            if (response.success) {
              const $priceElement = $(
                `.package-buttons .form-button[data-package="${packageType}"] .package-price`
              );
              const price =
                packageType === "dnevni"
                  ? `rsd po danu ${response.data.formatted_price}`
                  : response.data.formatted_price;

              $priceElement.html(price);
            }
          },
        });
      });
    }

    handleDateSelection(dateText) {
      const isDaily = this.state.package === "dnevni";

      if (isDaily) {
        const dateIndex = this.state.dates.indexOf(dateText);
        if (dateIndex > -1) {
          this.state.dates.splice(dateIndex, 1);
        } else {
          this.state.dates.push(dateText);
        }
        this.updateSelectedDatesDisplay();
      } else {
        this.state.dates = [dateText];
        const endDate = this.calculateEndDate(dateText);
        const displayText = `${mealTranslations.od} ${this.formatDate(
          dateText
        )} ${mealTranslations.do} ${this.formatDate(endDate)}`;
        this.updateDateDisplay(displayText);
      }

      // Dodajte console.log da vidite stanje
      console.log("Current dates:", this.state.dates);

      this.updatePrice();
      this.validateForm();
    }
    calculateEndDate(startDate) {
      const workingDaysMap = {
        nedeljni5: 4,
        nedeljni6: 5,
        mesecni20: 19,
        mesecni24: 23,
      };

      const workingDays = workingDaysMap[this.state.package];
      if (!workingDays) return startDate;

      const [day, month, year] = startDate.split("-").map(Number);
      let currentDate = new Date(year, month - 1, day);
      let addedDays = 0;
      const isWeekdaysOnly = ["nedeljni5", "mesecni20"].includes(
        this.state.package
      );

      while (addedDays < workingDays) {
        currentDate.setDate(currentDate.getDate() + 1);
        const dayOfWeek = currentDate.getDay();

        if (dayOfWeek !== 0 && !(isWeekdaysOnly && dayOfWeek === 6)) {
          addedDays++;
        }
      }

      return currentDate;
    }

    CopyupdateDateRangeDisplay(startDate, endDate) {
      $(".date-selection").html(`
        <div class="date-range-wrapper">
          <div class="date-range-display">
            <div class="calendar-trigger active">
              <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.66667 6.33333V1M18.3333 6.33333V1M6.33333 11.6667H19.6667M3.66667 25H22.3333C23.0406 25 23.7189 24.719 24.219 24.219C24.719 23.7189 25 23.0406 25 22.3333V6.33333C25 5.62609 24.719 4.94781 24.219 4.44772C23.7189 3.94762 23.0406 3.66667 22.3333 3.66667H3.66667C2.95942 3.66667 2.28115 3.94762 1.78105 4.44772C1.28095 4.94781 1 5.62609 1 6.33333V22.3333C1 23.0406 1.28095 23.7189 1.78105 24.219C2.28115 24.719 2.95942 25 3.66667 25Z" stroke="#2D2D2D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span class="start-date">od ${this.formatDate(startDate)}</span>
              <span class="calendar-label">${mealTranslations.calendar}</span>
            </div>
          </div>
          <span class="end-date-display">do ${this.formatDate(endDate)}</span>
        </div>
      `);
    }

    updateSelectedDatesDisplay() {
      if (this.state.package === "dnevni" && this.state.dates.length > 0) {
        // Prvo sortiramo datume
        const sortedDates = [...this.state.dates].sort((a, b) => {
          const [dayA, monthA, yearA] = a.split("-").map(Number);
          const [dayB, monthB, yearB] = b.split("-").map(Number);
          return (
            new Date(yearA, monthA - 1, dayA) -
            new Date(yearB, monthB - 1, dayB)
          );
        });

        // Grupišemo datume po mesecima
        const groupedDates = sortedDates.reduce((groups, dateStr) => {
          const [day, month, year] = dateStr.split("-");
          const monthKey = `${month}-${year}`;
          if (!groups[monthKey]) {
            groups[monthKey] = [];
          }
          groups[monthKey].push(parseInt(day));
          return groups;
        }, {});

        // Formatiramo prikaz
        let displayText = "";
        Object.entries(groupedDates).forEach(([monthKey, days], index) => {
          const [month, year] = monthKey.split("-");
          const monthName = this.getMonthName(parseInt(month) - 1);

          displayText += days.join(". ") + ". " + monthName;

          if (index < Object.keys(groupedDates).length - 1) {
            displayText += " ; ";
          }
        });

        this.updateDateDisplay(displayText);
      }
    }
    updateDateDisplay(displayText) {
      if (!$("#deliveryDate").length) {
        $(".date-selection").html(`
          <div class="calendar-trigger active">
            <div class="date-range-wrapper">
              <div class="date-range-display">
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M7.66667 6.33333V1M18.3333 6.33333V1M6.33333 11.6667H19.6667M3.66667 25H22.3333C23.0406 25 23.7189 24.719 24.219 24.219C24.719 23.7189 25 23.0406 25 22.3333V6.33333C25 5.62609 24.719 4.94781 24.219 4.44772C23.7189 3.94762 23.0406 3.66667 22.3333 3.66667H3.66667C2.95942 3.66667 2.28115 3.94762 1.78105 4.44772C1.28095 4.94781 1 5.62609 1 6.33333V22.3333C1 23.0406 1.28095 23.7189 1.78105 24.219C2.28115 24.719 2.95942 25 3.66667 25Z" stroke="#2D2D2D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                ${
                  displayText
                    ? `<span class="start-date has-date">${displayText}</span>`
                    : `<span class="start-date"></span>`
                }
                <span class="calendar-label">${mealTranslations.calendar}</span>
              </div>
            </div>
          </div>
          <input type="text" id="deliveryDate" class="datepicker" readonly style="display: none;">
        `);
        this.initializeDatepicker();
      } else {
        const $startDate = $(".start-date");
        if (displayText) {
          $startDate.text(displayText).addClass("has-date");
        } else {
          $startDate.text("").removeClass("has-date");
        }
      }
    }

    // Dodajemo helper metod za nazive meseci
    getMonthName(monthIndex) {
      return mealTranslations.months[monthIndex];
    }

    formatDate(date) {
      try {
        if (typeof date === "string") {
          const [day, month, year] = date.split("-").map(Number);
          return `${day}. ${mealTranslations.months[month - 1]}`;
        }

        if (date instanceof Date) {
          return `${date.getDate()}. ${
            mealTranslations.months[date.getMonth()]
          }`;
        }

        return "";
      } catch (error) {
        console.error("Error formatting date:", error);
        return "";
      }
    }

    updatePrice() {
      if (!this.state.gender || !this.state.calories || !this.state.package)
        return;

      $.ajax({
        url: meal_plan_vars.ajax_url,
        type: "POST",
        data: {
          action: "get_meal_plan_price",
          nonce: meal_plan_vars.nonce,
          pll_lang: mpPllLang(),
          menu_type: this.state.menuType,
          gender: this.state.gender,
          calories: this.state.calories,
          package: this.state.package,
          days_count:
            this.state.package === "dnevni" ? this.state.dates.length : 1,
        },
        success: (response) => {
          if (response.success) {
            $(".meal-plan-price").html(response.data.formatted_price);
            $(".meal-plan-price-wrapper").show();
          }
        },
      });
    }
    /**
     * WooCommerce kao na single product: pun POST na istu stranicu, pa server radi
     * wp_safe_redirect na korpu/checkout — sesija i kolačići ostaju isti (nema AJAX + JS redirect).
     */
    prepareMealPlanPostFields($form, buyNow) {
      $form.find("input.kombo-mp-date").remove();

      (this.state.dates || []).forEach((d) => {
        $("<input>", {
          type: "hidden",
          name: "dates[]",
          value: d,
          class: "kombo-mp-date",
        }).appendTo($form);
      });

      let $flag = $form.find('input[name="kombo_meal_plan_submit"]');
      if (!$flag.length) {
        $flag = $(
          '<input type="hidden" name="kombo_meal_plan_submit" value="0">'
        ).appendTo($form);
      }
      $flag.val("1");

      let $bn = $form.find('input[name="buy_now"]');
      if (!$bn.length) {
        $bn = $('<input type="hidden" name="buy_now" value="0">').appendTo(
          $form
        );
      }
      $bn.val(buyNow ? "1" : "0");

      let $pll = $form.find('input[name="pll_lang"]');
      if (!$pll.length) {
        $pll = $('<input type="hidden" name="pll_lang" value="">').appendTo(
          $form
        );
      }
      $pll.val(mpPllLang());
    }

    submitMealPlanToWooCommerce(buyNow = false) {
      mpTrace("form_post_submit", { buyNow, state: { ...this.state } });
      if (!this.validateForm()) {
        alert(mealTranslations.fillAllFields);
        return;
      }

      const $form = $("#mealPlanForm");
      if (!$form.length) {
        alert(mealTranslations.serverError);
        return;
      }

      const $button = buyNow
        ? $(".submit-button.buy-now")
        : $(".submit-button.add-to-cart");
      const $otherButton = buyNow
        ? $(".submit-button.add-to-cart")
        : $(".submit-button.buy-now");
      const loadingText = buyNow
        ? mealTranslations.processing
        : mealTranslations.addingToCart;

      $button.prop("disabled", true).text(loadingText);
      $otherButton.prop("disabled", true);

      this.prepareMealPlanPostFields($form, buyNow);
      // DOM .submit() ne okida jQuery "submit" handler (koji i dalje blokira slučajni submit).
      $form[0].submit();
    }
    initializeDatepicker() {
      if (!$("#deliveryDate").length) {
        $("<input>", {
          type: "text",
          id: "deliveryDate",
          class: "datepicker",
          readonly: true,
          style: "display: none;",
        }).appendTo(".date-selection");
      }

      const $datepicker = $("#deliveryDate");

      // Destroy existing datepicker
      if ($datepicker.hasClass("hasDatepicker")) {
        $datepicker.datepicker("destroy");
      }

      const isDaily = this.state.package === "dnevni";
      const isWeekdaysOnly =
        this.state.package === "nedeljni5" ||
        this.state.package === "mesecni20";

      // Provera cutoff vremena
      const now = new Date();
      const cutoffTime = new Date();
      cutoffTime.setHours(20, 0, 0, 0);

      // Ako je prošlo 20h, minDate je 2 (prekosutra), inače je 1 (sutra)
      const minDate = now > cutoffTime ? 2 : 1;

      $datepicker.datepicker({
        dateFormat: "dd-mm-yy",
        minDate: minDate,
        changeMonth: false,
        changeYear: false,
        dayNamesMin: ["Ned", "Pon", "Uto", "Sre", "Čet", "Pet", "Sub"],
        beforeShow: (input, inst) => {
          if (isWeekdaysOnly) {
            inst.dpDiv.addClass("weekdays-only");
          } else {
            inst.dpDiv.removeClass("weekdays-only");
          }
        },
        beforeShowDay: (date) => {
          const day = date.getDay();
          const dateString = $.datepicker.formatDate("dd-mm-yy", date);

          // Nedelja je uvek onemogućena
          if (day === 0) {
            return [false, ""];
          }

          // Za nedeljni5 i mesecni20 onemogućavamo subotu
          if (isWeekdaysOnly && day === 6) {
            return [false, ""];
          }

          // Za dnevni paket, označavamo selektovane datume
          if (isDaily) {
            const isSelected = this.state.dates.includes(dateString);
            return [true, isSelected ? "ui-state-highlight" : ""];
          }

          return [true, ""];
        },
        onSelect: (dateText) => this.handleDateSelection(dateText),
      });

      // Update calendar trigger
      $(document)
        .off("click", ".calendar-trigger")
        .on("click", ".calendar-trigger", () => {
          if ($datepicker.length) {
            $datepicker.datepicker("show");
          }
        });
    }
  }

  // Initialize form
  const mealPlanForm = new MealPlanForm();

  // Initialize intersection observer for price display
  const price = document.querySelector(".meal-plan-price");
  const sideContainer = document.querySelector(".side_container");

  if (price && sideContainer) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          sideContainer.classList.toggle("faded-out", entry.isIntersecting);
        });
      },
      { threshold: 0 }
    );

    observer.observe(price);
  }
});
