// ================= GLOBAL =================
let startTime = 0;
let isSimulating = false;
let jenisRisikoAktif = "";

const aiLog = document.getElementById("aiLog");
const hazardText = document.getElementById("hazardText");
const API_URL = "api.php";

// ================= HELPER =================
function showError(msg) {
  console.error(msg);
  alert(msg);
}

// ================= 1. LOAD PESERTA =================
async function loadPesertaToSelect() {
  const select = document.getElementById("pilihPeserta");
  if (!select) return;

  try {
    const res = await fetch(`${API_URL}?target=users`);
    const data = await res.json();

    select.innerHTML = '<option value="">-- Pilih Peserta --</option>';

    data.forEach((p) => {
      const option = document.createElement("option");
      option.value = p.id_user;
      option.textContent = `${p.nama} (${p.jabatan})`;
      select.appendChild(option);
    });
  } catch (err) {
    showError("Gagal load peserta!");
  }
}

// ================= 2. TAMBAH USER =================
async function tambahUser() {
  const nama = document.getElementById("namaInput").value.trim();
  const jabatan = document.getElementById("jabatanInput").value;

  if (!nama || !jabatan) {
    alert("Isi semua data!");
    return;
  }

  try {
    const formData = new FormData();
    formData.append("nama", nama);
    formData.append("jabatan", jabatan);

    const res = await fetch("tambah_proses.php", {
      method: "POST",
      body: formData,
    });

    if (!res.ok) throw new Error("Gagal tambah user");

    document.getElementById("namaInput").value = "";
    document.getElementById("jabatanInput").value = "";

    loadPesertaToSelect();
    tampilkanRiwayat();

    alert("Peserta berhasil ditambahkan!");
  } catch (err) {
    showError("Gagal menambahkan peserta!");
  }
}

// ================= 3. MULAI SIMULASI =================
function startSim() {
  const selectPeserta = document.getElementById("pilihPeserta");

  if (selectPeserta.value === "") {
    alert("Silahkan pilih peserta dulu!");
    return;
  }

  let infoPeserta = selectPeserta.options[selectPeserta.selectedIndex].text;

  isSimulating = true;
  startTime = 0;

  const model = document.getElementById("hazardModel");
  document.getElementById("hazardText").style.display = "none";
  document.getElementById("simWindow").style.border = "3px solid #3498db";

  // Kosongkan rintangan lama dan pastikan terlihat
  model.innerHTML = "";
  model.setAttribute("visible", "true");

  // Logika penentuan rintangan berdasarkan jabatan peserta
  if (infoPeserta.includes("Operator Mesin")) {
    jenisRisikoAktif = "Tumpahan Oli";

    // Kita buat tumpahan oli kuning di lantai
    let oli = document.createElement("a-cylinder");
    oli.setAttribute("radius", "0.6");
    oli.setAttribute("height", "0.02");
    oli.setAttribute("color", "#f1c40f");
    oli.setAttribute("position", "1 0.01 -2");
    model.appendChild(oli);
  } else if (infoPeserta.includes("Damkar Intern")) {
    jenisRisikoAktif = "Kebocoran Pipa Gas";

    // Matikan bentuk bawaan parent agar tidak muncul kotak abu-abu lagi
    model.removeAttribute("geometry");
    model.removeAttribute("color");

    // Buat Pipa Gas
    let pipa = document.createElement("a-cylinder");
    pipa.setAttribute("radius", "0.08");
    pipa.setAttribute("height", "4");
    pipa.setAttribute("color", "#7f8c8d");
    pipa.setAttribute("position", "0 2.2 -3");
    pipa.setAttribute("rotation", "0 0 90");

    // Buat Semburan Bola Oranye
    let semburan = document.createElement("a-sphere");
    semburan.setAttribute("radius", "0.2");
    semburan.setAttribute("color", "#e67e22");
    semburan.setAttribute("position", "0 2.2 -3");
    semburan.setAttribute("opacity", "0.7");
    semburan.setAttribute(
      "animation",
      "property: scale; from: 1 1 1; to: 3 3 3; dur: 500; loop: true; dir: alternate",
    );

    model.appendChild(pipa);
    model.appendChild(semburan);
  } else {
    jenisRisikoAktif = "Korsleting Listrik";

    // Kita buat bola merah kecil di dinding
    let korsleting = document.createElement("a-sphere");
    korsleting.setAttribute("radius", "0.2");
    korsleting.setAttribute("color", "#ff0000");
    korsleting.setAttribute("position", "-0.5 1 -2.8");
    model.appendChild(korsleting);
  }

  let jeda = Math.floor(Math.random() * 3000) + 2000;

  setTimeout(() => {
    if (isSimulating) {
      document.getElementById("hazardText").innerHTML =
        "⚠️ BAHAYA: " + jenisRisikoAktif.toUpperCase();
      document.getElementById("hazardText").style.display = "block";
      document.getElementById("simWindow").style.border = "3px solid #e74c3c";

      startTime = new Date().getTime();
    }
  }, jeda);
}

// ================= 4. TINDAKAN DARURAT =================
async function ambilTindakan() {
  let idPeserta = document.getElementById("pilihPeserta").value;
  if (waktuBahaya === 0) {
    alert("Belum ada bahaya!");
    return;
  }

  let waktuSkrg = new Date().getTime();
  let respon = (waktuSkrg - waktuBahaya) / 1000;
  isRunning = false;

  // TAMBAHKAN INI: Hentikan semua suara yang sedang berputar
  document.getElementById("suaraOli").pause();
  document.getElementById("suaraGas").pause();
  document.getElementById("suaraKorslet").pause();

  // Kembalikan durasi audio ke detik ke-0 agar kalau diulang tidak meneruskan
  document.getElementById("suaraOli").currentTime = 0;
  document.getElementById("suaraGas").currentTime = 0;
  document.getElementById("suaraKorslet").currentTime = 0;

  // Kode form submit Anda yang kemarin...
  document.getElementById("formIdUser").value = idPeserta;
  document.getElementById("formRisiko").value = jenisRisikoAktif;
  document.getElementById("formRespon").value = respon.toFixed(2);

  document.getElementById("formSkor").submit();
}

// ================= 5. TAMPILKAN RIWAYAT =================
async function tampilkanRiwayat() {
  const tbody = document.querySelector("#tabelRiwayat tbody");
  if (!tbody) return;

  try {
    const res = await fetch(`${API_URL}?target=riwayat`);
    const data = await res.json();

    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML =
        "<tr><td colspan='5' style='text-align:center; color:#95a5a6;'>Belum ada riwayat simulasi.</td></tr>";
      return;
    }

    data.forEach((item) => {
      const statusColor =
        item.status_kelulusan === "LULUS" ? "#00ff41" : "#e74c3c";

      const row = `
        <tr>
          <td>${item.nama}</td>
          <td>${item.jenis_risiko}</td>
          <td>${item.waktu_respon}s</td>
          <td style="color:${statusColor}; font-weight:bold;">${item.status_kelulusan}</td>
          <td style="text-align: center;">
            <button class="btn btn-outline" onclick="hapusSatu(${item.id_simulasi})" style="cursor:pointer; border:none; background:transparent;">
              ❌
            </button>
          </td>
        </tr>
      `;
      tbody.innerHTML += row;
    });
  } catch (err) {
    showError("Gagal load riwayat!");
  }
}

// ================= 6. HAPUS SEMUA =================
async function hapusSemua() {
  if (!confirm("Yakin ingin menghapus semua riwayat?")) return;

  try {
    const res = await fetch(API_URL, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ mode: "all" }),
    });

    const result = await res.json();
    if (result.error) throw new Error(result.error);

    tampilkanRiwayat();
    alert("Semua data berhasil dihapus!");
  } catch (err) {
    showError("Gagal menghapus semua data!");
  }
}

// ================= 7. HAPUS PER ITEM =================
async function hapusSatu(id) {
  if (!confirm("Hapus data ini?")) return;

  try {
    const res = await fetch(API_URL, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: id }),
    });

    const result = await res.json();
    if (result.error) throw new Error(result.error);

    tampilkanRiwayat();
  } catch (err) {
    showError("Gagal menghapus data!");
  }
}

// ================= INIT =================
document.addEventListener("DOMContentLoaded", () => {
  // Kode load peserta dll...

  // PAKSA A-FRAME UNTUK RE-RENDER
  setTimeout(() => {
    const scene = document.querySelector("a-scene");
    if (scene) {
      scene.resize();
    }
  }, 1000); // Tunggu 1 detik setelah halaman dimuat
});

// 1. Membuat Geometri Kubus (Ukuran: 1x1x1)
const geometry = new THREE.BoxGeometry(1, 1, 1);

// 2. Membuat Material untuk masing-masing objek
const materialOli = new THREE.MeshStandardMaterial({
  color: 0x111111, // Hitam pekat untuk oli
  roughness: 0.1, // Mengkilap seperti cairan
  metalness: 0.1,
});

const materialKorsleting = new THREE.MeshStandardMaterial({
  color: 0xffaa00, // Oranye/Kuning untuk area listrik
  emissive: 0xff5500, // Membuatnya tampak menyala
  roughness: 0.5,
});
