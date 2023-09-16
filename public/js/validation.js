$(document).ready(function () { /**
     * Validate the form
     */
    $("#cancel").click(function () {
        if (document.getElementById("inputAmount")) {
            document.getElementById("inputAmount").required = false;
        }
        if (document.getElementById("inputDate")) {
            document.getElementById("inputDate").required = false;
        }
        if (document.getElementById("inputPaymentMethod")){
            document.getElementById("inputPaymentMethod").required = false;
        }
        if (document.getElementById("inputCategory")) {
            document.getElementById("inputCategory").required = false;
        }
        if (document.getElementById("category")) {
            document.getElementById("category").required = false;
        }
        if (document.getElementById("choosePaymentMethod")) {
            document.getElementById("choosePaymentMethod").required = false;
        }
    })

    $("#validation").validate({
        rules: {
            expense_comment: {
                maxlength: 100
            },
            income_comment: {
                maxlength: 100
            }
        },

        highlight: function (element, errorClass, validClass) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        }

    });
});