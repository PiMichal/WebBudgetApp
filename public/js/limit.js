const numberOfInputs = document.querySelectorAll(".my_select").length;


for (let i = 0; i < numberOfInputs; i++) {

    document.querySelectorAll(".my_select")[i].addEventListener("input", async () => {

        const category = document.querySelector('#inputCategory').value;
        const date = document.querySelector('#inputDate').value;

        const limitValue = await getLimitForCategory(category);
        const monthlyValue = await getMonthlyExpenses(category, date);
        const amount = inputAmount();

        if (category != "→ Choose category ←") {
            showLimitOfThisCategory(limitValue, category);
        }
        
        if (category != "→ Choose category ←") {
            showMonthlyExpensesInThisCategory(monthlyValue, category)
        }

        const cashLeft = limitValue - monthlyValue;
        if (limitValue == 0 && category != "→ Choose category ←") {
            showCashLeft(limitValue, amount, category);
        } else if (category != "→ Choose category ←") {
            showCashLeft(cashLeft, (cashLeft - amount), category);
        }
    })


}

const showLimitOfThisCategory = (limitValue, category) => {
    
    const textLimit = document.querySelector('.limitInfo');
    
    if (limitValue == 0) {
        textLimit.innerText = `No limit has been set for the ${category} category.`;
    } else {
        textLimit.innerText = `You set the limit ${limitValue} PLN monthly for the ${category} category.`;
    }
}

const showMonthlyExpensesInThisCategory = (monthlyValue, category) => {

    const textMonthly = document.querySelector('.limitValue');

    if (monthlyValue == 0) {
        textMonthly.innerText = `You did not spend any money for the ${category} category this month.`;
    } else {
        textMonthly.innerText = `You spent ${monthlyValue} PLN this month for the ${category} category.`;
    }
}

const showCashLeft = (cashLeft, inputAmount, category) => {

    const textCashLeft = document.querySelector('.cashLeft');

    if (cashLeft == 0 ) {
        textCashLeft.innerText = `No spending limit has been set for the ${category} category.`;
        textCashLeft.style.backgroundColor = "DarkCyan";
    } else if (inputAmount >= 0) {
        textCashLeft.innerText = `Limit balance after operation: ${inputAmount} PLN`;
        textCashLeft.style.backgroundColor = "green";
    } else {
        textCashLeft.innerText = `Limit balance after operation: ${inputAmount} PLN`;
        textCashLeft.style.backgroundColor = "red";
    }
    

}

const getLimitForCategory = async (category) => {
    
    category = category.replace(/ /g, '_');

    try {
        const res = await fetch(`../api/limit/${category}`);
        return await res.json();

    } catch (e) {
        console.log('ERROR', e);
    }
}

const getMonthlyExpenses = async (category, date) => {
    
    category = category.replace(/ /g, '_');

    try {
        const res = await fetch(`../api/limitValue/${category}/${date}`);
        return await res.json();

    } catch (e) {
        console.log('ERROR', e);
    }
}

const inputAmount = () => {

        return document.querySelector('#inputAmount').value;
}

