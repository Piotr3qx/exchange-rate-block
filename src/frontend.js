class ExchangeRateDataPicker {
    constructor(dateInput) {
        this.dateInput = dateInput;
        this.dateInputCurrency = this.dateInput.dataset.currency
        this.getComplementaryElements(this.dateInput.dataset.id);
    }

    getComplementaryElements(id) {
        this.exchchangeRateContainer = document.querySelector(`#${id}`);
        this.exchchangeRateDate = document.querySelector(`#${id} .exchangerate__date_value`);
        this.exchchangeRateRate = document.querySelector(`#${id} .exchangerate__rate_value`);
        this.exchchangeRateError = document.querySelector(`#${id} .exchangerate__error`);
    }

    checkDate() {
        const today = new Date();
        const checkingDate = new Date(this.dateInput.value);

        if (today.getTime() < checkingDate.getTime()) {
            return false;
        } else {
            return true;
        }
    }

    getNewDateValue() {
        const isCorrectDate = this.checkDate();

        if (!isCorrectDate) {
            this.setNewValues("Wybrana data nie może być późniejsza niż dzisiejsza.", "n/a")
        } else {
            this.makeAjaxCall()
        }
    }

    makeAjaxCall() {
        fetch(exchangeRateData.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache',
            },
            body: new URLSearchParams({
                action: 'get_ajax_rate_data',
                date: this.dateInput.value,
                currency: this.dateInputCurrency,
                ajaxNonce: exchangeRateData.ajaxnonce
            })
        }).then(response => {
            return response.json()
        }).then(response => {
            const { data } = response
            this.setNewValues(data.error_message, data.rate, response.success)
        }).catch((error) => console.log(error));
    }

    setNewValues(errorMsg, newRate, status) {
        this.exchchangeRateError.innerText = errorMsg;
        this.exchchangeRateRate.innerText = newRate;
        this.exchchangeRateDate.innerText = this.dateInput.value;

        if(status === true) {
            this.exchchangeRateContainer.classList.remove('exchangerate__is-error');
        } else {
            this.exchchangeRateContainer.classList.add('exchangerate__is-error');
        }
    }
}

if (document.querySelectorAll('.exchangerate__date-picker')) {
    const exchangeRateDataPickers = document.querySelectorAll('.exchangerate__date-picker');
    exchangeRateDataPickers.forEach(input => {
        const dataPicker = new ExchangeRateDataPicker(input);
        input.addEventListener("change", (e) => {
            dataPicker.getNewDateValue();
        })
    });
}