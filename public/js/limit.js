var numberOfInputs = document.querySelectorAll(".my_select").length;

for (var i = 0; i < numberOfInputs; i++) {

    document.querySelectorAll(".my_select")[i].addEventListener("change", async () => {

        const category = document.querySelector('#inputCategory').value;
        const date = document.querySelector('#inputDate').value;

        const limitValue = await getLimitForCategory(category);
        const monthlyValue = await getMonthlyExpenses(category, date);
        const amount = await inputAmount();
        

        if (category != "→ Choose category ←") {
            showLimitOfThisCategory(limitValue, category);
        }
        
        if (category != "→ Choose category ←") {
            showMonthlyExpensesInThisCategory(monthlyValue, category)
        }

        const cashLeft = limitValue - monthlyValue;
        if (limitValue === 0 && category != "→ Choose category ←") {
            showCashLeft(limitValue, amount, category);
        } else if (category != "→ Choose category ←") {
            showCashLeft(cashLeft, (cashLeft - amount), category);
        }
    })


}
const showLimitOfThisCategory = (limitValue, category) => {
    
    const textLimitValue = document.querySelector('.limitInfo');
    
    if (limitValue === 0) {
        textLimitValue.innerText = `No limit has been set for the ${category} category.`;
    } else {
        textLimitValue.innerText = `You set the limit ${limitValue} PLN monthly for the ${category} category.`;
    }
}

const showMonthlyExpensesInThisCategory = (monthlyValue, category) => {

    const textMonthlyValue = document.querySelector('.limitValue');
    if (monthlyValue === 0) {
        textMonthlyValue.innerText = `You did not spend any money for the ${category} category this month.`;
    } else {
        textMonthlyValue.innerText = `You spent ${monthlyValue} PLN this month for the ${category} category.`;
    }
}

const showCashLeft = (cashLeft, inputAmount, category) => {
    const textValueCashLeft = document.querySelector('.cashLeft');
    if (cashLeft === 0 ) {
        textValueCashLeft.innerText = `No spending limit has been set for the ${category} category.`;
        textValueCashLeft.style.backgroundColor = "DarkCyan";
    } else if (inputAmount >= 0) {
        textValueCashLeft.innerText = `Limit balance after operation: ${inputAmount} PLN`;
        textValueCashLeft.style.backgroundColor = "green";
    } else {
        textValueCashLeft.innerText = `Limit balance after operation: ${inputAmount} PLN`;
        textValueCashLeft.style.backgroundColor = "red";
    }
    

}

const getLimitForCategory = async (category) => {
    
    category = category.replace(/ /g, '_');

    try {
        const res = await fetch(`../api/limit/${category}`);
        const data = await res.json();
        return data;

    } catch (e) {
        console.log('ERROR', e);
    }
}

const getMonthlyExpenses = async (category, date) => {
    
    category = category.replace(/ /g, '_');

    try {
        const res = await fetch(`../api/limitValue/${category}/${date}`);
        const data = await res.json();
        return data;

    } catch (e) {
        console.log('ERROR', e);
    }
}

const inputAmount = async () => {
    try {
        const amount = document.querySelector('#inputAmount').value;
        return amount;

    } catch (e) {
        console.log('ERROR', e);
    }
}

