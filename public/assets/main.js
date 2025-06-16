function calculateLoss() {
    const inputLoss = parseFloat(document.getElementById("inputLoss").value);
    const splitterLoss = parseFloat(document.getElementById("splitter").value);
    const spliceLoss = parseFloat(document.getElementById("spliceLoss").value);
    const connectorLoss = parseFloat(document.getElementById("connectorLoss").value);
    const meter = parseFloat(document.getElementById("cableLength").value);
    const cableLoss = 0.3 * (meter / 1000);
    if (isNaN(inputLoss)) return;
    const total = inputLoss - (cableLoss + (spliceLoss || 0) + (connectorLoss || 0) + (splitterLoss || 0));
    const resultElement = document.getElementById("resultLoss");
    const statusElement = document.getElementById("statusText");
    resultElement.textContent = `${total.toFixed(2)} dB`;
    if (total <= -28.00) {
        statusElement.textContent = "Connection Lost";
        statusElement.classList.remove("text-warning");
        statusElement.classList.add("text-danger");
    } else {
        statusElement.textContent = "Connection Success";
        statusElement.classList.remove("text-danger");
        statusElement.classList.add("text-warning");
    }
}
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener("input", calculateLoss);
    });
    document.getElementById("splitter").addEventListener("change", calculateLoss);
    const slider = document.getElementById("cableLength");
    const input = document.getElementById("cableLengthInput");
    const meterDisplay = document.getElementById("meterDisplay");
    const kmDisplay = document.getElementById("kmDisplay");
    function sync(val) {
        slider.value = val;
        input.value = val;
        meterDisplay.textContent = `${val} m`;
        kmDisplay.textContent = `${(val / 1000).toFixed(2)} km`;
        calculateLoss();
    }
    slider.addEventListener("input", () => sync(slider.value));
    input.addEventListener("input", () => sync(input.value));
    sync(slider.value);
});
