let inputSign = '+'; // Menyimpan tanda input loss (+ atau -) default-nya adalah '+'

// Fungsi untuk mengganti tanda input loss (+ atau -)
function setSign(sign) {
    inputSign = sign; // Simpan tanda yang dipilih
    document.getElementById("plusMinusBtn").textContent = sign; // Update tombol dropdown jadi tanda yang dipilih
    calculateLoss(); // Langsung hitung ulang redaman
}

// Fungsi untuk menampilkan nilai kabel dalam meter dan kilometer
function updateCableDisplay(val) {
    const km = (val / 1000).toFixed(2); // Konversi meter ke kilometer
    document.getElementById("meterDisplay").textContent = `${val} m`; // Update badge meter
    document.getElementById("kmDisplay").textContent = `${km} km`;     // Update badge kilometer
}

function updateSliderColor(val) {
    const min = 1;
    const max = 10000;
    const percent = ((val - min) / (max - min)) * 100;

    const slider = document.getElementById("cableLength");
    const color = `linear-gradient(90deg, rgb(16, 188, 105) ${percent}%, rgb(215, 210, 210) ${percent}%)`;
    slider.style.background = color;
}

// Fungsi utama untuk menghitung total redaman optik
function calculateLoss() {
    const inputField = document.getElementById("inputLoss");
    const value = inputField.value;

    if (value === "") {
        document.getElementById("resultLoss").textContent = "-";
        document.getElementById("statusText").textContent = "Waiting for input...";
        return; // Jika kosong, tidak lanjut hitung
    }

    const rawInput = parseFloat(value); // Konversi string ke float
    const inputLoss = inputSign === '-' ? -rawInput : rawInput; // Terapkan tanda - jika dipilih

    // Ambil semua input lainnya
    const splitterLoss = parseFloat(document.getElementById("splitter").value) || 0;
    const spliceLoss = parseFloat(document.getElementById("spliceLoss").value) || 0;
    const connectorLoss = parseFloat(document.getElementById("connectorLoss").value) || 0;
    const meter = parseFloat(document.getElementById("cableLength").value) || 0;

    const cableLossPerKm = parseFloat(document.getElementById("cableType").value) || 0.3;
    const cableLoss = cableLossPerKm * (meter / 1000);
    // Rumus redaman kabel: 0.3 dB per km

    // Hitung total redaman akhir
    const total = inputLoss - (cableLoss + spliceLoss + connectorLoss + splitterLoss);

    // Tampilkan total redaman ke layar
    document.getElementById("resultLoss").textContent = `${total.toFixed(2)} dB`;

    // Tentukan status koneksi berdasarkan nilai total
    const statusElement = document.getElementById("statusText");

    if (total >= -8.0) {
        statusElement.textContent = "Connection Too Strong 😕";
        statusElement.className = "text-warning fw-bold";
    } else if (total <= -28.0) {
        statusElement.textContent = "Connection Too Low ❌";
        statusElement.className = "text-danger fw-bold";
    } else if (total <= -24.0) {
        statusElement.textContent = "Connection Low 😅";
        statusElement.className = "fw-bold";
        statusElement.style.color = "#ff82bd";
    } else {
        statusElement.textContent = "Connection Good 👍";
        statusElement.className = "text-success fw-bold";
    }
}

document.getElementById("cableType").addEventListener("change", () => {
    const val = document.getElementById("cableType").value;
    const badge = document.getElementById("lossPerKmInfo");

    badge.textContent = `${val} dB/km`;

    // Ubah warna badge sesuai pilihan kabel
    if (val === "0.3") {
        badge.className = "badge bg-dark ms-2"; // Dropcore
    } else if (val === "0.2") {
        badge.className = "badge bg-warning text-dark ms-2"; // Patchcord
    }

    calculateLoss();
});


document.getElementById("cableLength").addEventListener("input", () => {
    const val = document.getElementById("cableLength").value;
    document.getElementById("cableLengthInput").value = val;
    updateCableDisplay(val);
    updateSliderColor(val); // <--- ini penting!
    calculateLoss();
});

document.getElementById("cableLengthInput").addEventListener("input", () => {
    const val = document.getElementById("cableLengthInput").value;
    document.getElementById("cableLength").value = val;
    updateCableDisplay(val);
    updateSliderColor(val); // <--- ini juga!
    calculateLoss();
});

// Event lain: real-time input dari form
document.getElementById("inputLoss").addEventListener("input", calculateLoss);
document.getElementById("splitter").addEventListener("change", calculateLoss);
document.getElementById("spliceLoss").addEventListener("input", calculateLoss);
document.getElementById("connectorLoss").addEventListener("input", calculateLoss);

// Jalankan saat halaman dimuat
document.addEventListener("DOMContentLoaded", () => {
    const initialVal = document.getElementById("cableLength").value;
    updateCableDisplay(initialVal);
    updateSliderColor(initialVal);
    calculateLoss();
});
