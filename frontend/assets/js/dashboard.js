// K3-VirtuAI Dashboard JavaScript
let waktuBahaya = 0;
let isRunning = false;
let jenisRisikoAktif = "";
let countdownInterval = null;
let remedialData = {};

// Get user role from data attribute
const currentRole = document.body.getAttribute("data-user-role") || "mahasiswa";

// 1. DAFTAR 5 SKENARIO K3 RESMI
const skenarioK3 = [
  {
    jenis_risiko: "Kebocoran Pipa Gas",
    soal: "Sensor mendeteksi adanya kebocoran gas beracun di area produksi. Apa tindakan darurat pertama yang harus Anda lakukan?",
    pilihan: [
      {
        teks: "Segera keluar menuju titik kumpul mengikuti arah evakuasi, dan beri tahu tim K3/rekans kerja.",
        skor: 100,
        konsekuensi:
          "Tepat! Tanggap cepat, evakuasi terkoordinasi, mengurangi risiko paparan gas.",
        rekomendasi:
          "Jangan kembali sebelum area dinyatakan aman oleh petugas K3.",
      },
      {
        teks: "Mencari sumber kebocoran untuk mencoba menutupnya sendiri.",
        skor: 30,
        konsekuensi:
          "Fatal! Anda terpapar gas beracun konsentrasi tinggi dan pingsan.",
        rekomendasi:
          "Dilarang keras menangani kebocoran gas tanpa APD khusus (Breathing Apparatus).",
      },
      {
        teks: "Berteriak memanggil rekan kerja di dalam ruangan.",
        skor: 40,
        konsekuensi:
          "Berisiko! Anda membuang waktu evakuasi dan menghirup lebih banyak gas.",
        rekomendasi:
          "Gunakan tombol alarm evakuasi daripada berteriak untuk meminimalkan hirupan nafas.",
      },
      {
        teks: "Diam di tempat menunggu instruksi lebih lanjut lewat speaker.",
        skor: 50,
        konsekuensi:
          "Berbahaya! Gas terus menyebar sementara Anda tetap terpapar.",
        rekomendasi:
          "Dapatkan jarak aman dulu sebelum mengikuti arahan jika masih memungkinkan.",
      },
    ],
  },
  {
    jenis_risiko: "Korsleting Listrik",
    soal: "Terlihat percikan api dan kepulan asap dari panel listrik mesin utama. Apa tindakan Anda?",
    pilihan: [
      {
        teks: "Mengambil APAR jenis Foam untuk memadamkannya.",
        skor: 40,
        konsekuensi:
          "Berbahaya! APAR jenis foam berbasis air dan masih bisa menghantarkan listrik.",
        rekomendasi:
          "Gunakan APAR khusus kelas C (Kelistrikan) seperti CO2 agar tidak tersetrum.",
      },
      {
        teks: "Menyiram percikan api tersebut menggunakan air.",
        skor: 0,
        konsekuensi:
          "Fatal! Anda tersengat aliran listrik tegangan tinggi (Electrocuted).",
        rekomendasi:
          "Jangan sekali-kali menyiram kebakaran listrik dengan air. Gunakan APAR jenis CO2 atau Powder.",
      },
      {
        teks: "Mematikan saklar pusat (Main Breaker), kemudian panggil tim listrik/ K3 sambil menjaga keamanan area.",
        skor: 100,
        konsekuensi:
          "Tepat! Menghentikan sumber munculnya percikan dan menunggu teknisi.",
        rekomendasi:
          "Gunakan APAR CO2/residu rendah bila diperlukan, serta jangan ada air di area.",
      },
      {
        teks: "Memanggil teknisi listrik tanpa melakukan tindakan pengamanan.",
        skor: 30,
        konsekuensi: "Lambat dan berisiko jika sumber arus belum diputus.",
        rekomendasi: "Lakukan pemutusan arus mandiri sebelum menunggu teknisi.",
      },
    ],
  },
  {
    jenis_risiko: "Tumpahan Oli",
    soal: "Sebuah jeriken berisi cairan berbahaya (B3) tumpah meluas di lantai laboratorium. Bagaimana tindakan Anda?",
    pilihan: [
      {
        teks: "Meninggalkan ruangan dan mengunci pintunya agar uap tidak keluar.",
        skor: 40,
        konsekuensi:
          "Kurang tepat karena tidak ada langkah mitigasi kontak awal.",
        rekomendasi: "Isolasi area dan segera koordinasi dengan respons B3.",
      },
      {
        teks: "Mengelapnya langsung menggunakan kain pel biasa.",
        skor: 20,
        konsekuensi:
          "Fatal! Kain pel rusak dan uap korosif mengenai wajah Anda.",
        rekomendasi:
          "Bahan kimia B3 korosif memerlukan penanganan khusus, bukan alat pembersih rumah tangga.",
      },
      {
        teks: "Membilas tumpahan dengan air dalam jumlah banyak ke saluran drainase.",
        skor: 30,
        konsekuensi:
          "Buruk! Anda mencemari air tanah dan melanggar hukum lingkungan.",
        rekomendasi:
          "Dilarang membuang limbah B3 langsung ke saluran air umum tanpa dinetralkan.",
      },
      {
        teks: "Membatasi area, pakai APD lengkap, dan laporkan ke tim K3 untuk spill kit/bahan netralisasi.",
        skor: 100,
        konsekuensi:
          "Tepat! Baik keselamatan diri maupun tindakan teknis terkoordinasi.",
        rekomendasi:
          "Jangan gunakan pel biasa; gunakan peralatan khusus B3 dan pertahankan pengawasan area.",
      },
    ],
  },
  {
    jenis_risiko: "Kebakaran Area Panel",
    soal: "Muncul api kecil di tumpukan kardus dekat panel evakuasi. Apa yang harus Anda lakukan?",
    pilihan: [
      {
        teks: "Meniup api tersebut agar padam.",
        skor: 40,
        konsekuensi:
          "Buruk! Tiupan Anda justru memberikan oksigen tambahan dan membesarkan api.",
        rekomendasi:
          "Gunakan APAR atau karung goni basah untuk memutus rantai oksigen api.",
      },
      {
        teks: "Mengabaikannya karena apinya masih berukuran kecil.",
        skor: 10,
        konsekuensi:
          "Fatal! Dalam hitungan menit api membesar dan melahap seluruh ruangan.",
        rekomendasi:
          "Jangan pernah menyepelekan api sekecil apa pun di area industri.",
      },
      {
        teks: "Mengambil APAR CO2 atau powder dan memadamkan api kecil sesuai teknik PASS.",
        skor: 100,
        konsekuensi:
          "Tepat! Api dikendalikan cepat dan risiko menyebar berkurang.",
        rekomendasi:
          "Kondisi aman, lalu laporkan ke tim K3 dan pastikan sumber energi terisolasi.",
      },
      {
        teks: "Berlari kencang mencari hydrant gedung.",
        skor: 50,
        konsekuensi:
          "Kurang efektif! Hydrant terlalu besar untuk api kecil dan membuang waktu.",
        rekomendasi: "Gunakan APAR portabel untuk kebakaran tahap awal (mula).",
      },
    ],
  },
  {
    jenis_risiko: "Evakuasi Gempa Bumi",
    soal: "Terjadi gempa bumi berkekuatan cukup besar saat Anda berada di lantai 3. Tindakan terbaiknya adalah?",
    pilihan: [
      {
        teks: "Langsung berlari sekencang mungkin menuju tangga darurat.",
        skor: 40,
        konsekuensi:
          "Berisiko! Anda bisa terjatuh di tangga akibat guncangan yang belum berhenti.",
        rekomendasi:
          "Tunggu guncangan utama mereda sedikit sebelum melakukan pergerakan evakuasi.",
      },
      {
        teks: "Berlindung di bawah meja kokoh (Drop, Cover, Hold on), lalu evakuasi setelah guncangan mereda.",
        skor: 100,
        konsekuensi:
          "Tepat! Melindungi tubuh dari reruntuhan dan menjaga keamanan saat awal gempa.",
        rekomendasi:
          "Tetap di tempat terlindung hingga gempa berhenti, kemudian ke titik kumpul.",
      },
      {
        teks: "Menggunakan lift agar lebih cepat sampai ke lantai dasar.",
        skor: 0,
        konsekuensi:
          "Fatal! Listrik mati dan Anda terjebak di dalam lift yang macet.",
        rekomendasi:
          "Dilarang keras menggunakan lift saat terjadi gempa bumi atau kebakaran.",
      },
      {
        teks: "Berdiri diam di dekat tembok beton utama gedung.",
        skor: 60,
        konsekuensi:
          "Kurang tepat, karena Anda tidak memiliki perlindungan kepala dan tubuh dari benda jatuh.",
        rekomendasi:
          "Do-Not digunakan; choose Drop-Cover-Hold atau area terbuka setelah guncangan mereda.",
      },
    ],
  },
];

// Fungsi untuk shake kamera pada skenario gempa bumi
function shakeCamera(duration = 2000) {
  const camera = document.getElementById("cameraK3");
  if (!camera) return;

  const originalPosition = camera.getAttribute("position");
  const startTime = Date.now();
  const shakeStrength = 0.08; // Intensitas guncangan
  const shakeSpeed = 60; // ms per shake

  const shakeInterval = setInterval(() => {
    const elapsed = Date.now() - startTime;

    if (elapsed > duration) {
      clearInterval(shakeInterval);
      camera.setAttribute("position", originalPosition);
      return;
    }

    // Random offset untuk guncangan realistis
    const offsetX = (Math.random() - 0.5) * shakeStrength;
    const offsetY = (Math.random() - 0.5) * shakeStrength;

    const newPos = {
      x: parseFloat(originalPosition.x) + offsetX,
      y: parseFloat(originalPosition.y) + offsetY,
      z: parseFloat(originalPosition.z),
    };

    camera.setAttribute("position", `${newPos.x} ${newPos.y} ${newPos.z}`);
  }, shakeSpeed);
}

// Fungsi untuk auto-fail saat waktu habis
function autoFailTimeout() {
  isRunning = false;
  let idPeserta = document.getElementById("pilihPeserta").value;
  let kategoriRisiko = "Tinggi";
  let skor = 0;

  let konsekuensi =
    "Waktu habis! Anda tidak mengambil tindakan dengan cepat dan risiko meningkat.";
  let rekomendasi =
    "Tingkatkan kecepatan respons dan keputusan dalam kondisi darurat. Latihan berkala dan simulasi lebih sering akan membantu.";

  // AJAX Mengirim data nilai simulasi ke database
  let formData = new FormData();
  formData.append("id_user", idPeserta);
  formData.append("jenis_risiko", jenisRisikoAktif);
  formData.append("skor", skor);
  formData.append("status_kelulusan", "GAGAL");
  formData.append("rekomendasi", rekomendasi);
  formData.append("konsekuensi", konsekuensi);
  formData.append("tindakan_dipilih", "Tidak ada tindakan (Waktu Habis)"); // Tambahan untuk decision log
  formData.append("kategori_risiko", kategoriRisiko);

  fetch("../backend/api/simulation.php?action=save_result", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "sukses") {
        // Show personalized remedial modal instead of simple alert
        showRemedialModal({
          jenisRisiko: jenisRisikoAktif,
          skor: skor,
          kategoriRisiko: kategoriRisiko,
          konsekuensi: konsekuensi,
          rekomendasi: rekomendasi,
          tindakanDipilih: "Tidak ada tindakan (Waktu Habis)",
          alasanGagal: "Waktu habis tanpa tindakan pencegahan",
        });
      } else {
        alert("Error: Gagal menyimpan data timeout");
        window.location.reload();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan koneksi ke server!");
      window.location.reload();
    });
}

function startSim(forcedScenario = null) {
  let selectPeserta = document.getElementById("pilihPeserta");

  if (selectPeserta.value === "") {
    alert("Pilih peserta dulu!");
    return;
  }

  isRunning = true;
  waktuBahaya = 0;
  clearInterval(countdownInterval);

  let elHazard = document.getElementById("hazardText");
  let elTimer = document.getElementById("timerText");
  let elSimWin = document.getElementById("simWindow");
  let aiLog = document.getElementById("aiLog");

  if (elHazard) elHazard.style.display = "none";
  if (elTimer) elTimer.style.display = "none";
  if (elSimWin) elSimWin.style.border = "3px solid #3498db";

  // Reset visual A-Frame
  try {
    document.getElementById("efekGenanganOli").setAttribute("visible", "false");
    document.getElementById("efekListrik").setAttribute("visible", "false");
    document.getElementById("efekBocorPipa").setAttribute("visible", "false");
    document.getElementById("efekHancur").setAttribute("visible", "false");
  } catch (e) {
    console.log("Beberapa objek 3D tidak ditemukan.");
  }

  // MEMANGGIL SKENARIO
  // Jika ada forced scenario (dari retry), gunakan itu
  if (forcedScenario) {
    jenisRisikoAktif = forcedScenario;
    if (aiLog)
      aiLog.textContent = `> [Retry] Skenario remedial: ${jenisRisikoAktif}`;
  } else {
    // Mahasiswa: acak dari daftar skenario training
    // Admin/Dosen: gunakan skenario yang dipilih di pengaturan skenario
    if (currentRole === "mahasiswa") {
      const randomSkenario =
        skenarioK3[Math.floor(Math.random() * skenarioK3.length)];
      jenisRisikoAktif = randomSkenario.jenis_risiko;
      if (aiLog)
        aiLog.textContent = `> [Auto] Skenario mahasiswa: ${jenisRisikoAktif}`;
    } else {
      jenisRisikoAktif =
        document.body.getAttribute("data-admin-scenario") ||
        "Kebocoran Pipa Gas";
      if (aiLog)
        aiLog.textContent = `> [Admin] Skenario aktif: ${jenisRisikoAktif}`;
    }
  }

  let jeda = Math.floor(Math.random() * 2000) + 1000;

  setTimeout(() => {
    if (isRunning) {
      waktuBahaya = new Date().getTime();

      if (elHazard) {
        elHazard.innerHTML = "⚠️ BAHAYA: " + jenisRisikoAktif.toUpperCase();
        elHazard.style.display = "block";
      }
      if (elSimWin) elSimWin.style.border = "3px solid #e74c3c";

      // Trigger animasi 3D A-Frame
      try {
        if (jenisRisikoAktif === "Tumpahan Oli")
          document
            .getElementById("efekGenanganOli")
            .setAttribute("visible", "true");
        else if (jenisRisikoAktif === "Kebocoran Pipa Gas")
          document
            .getElementById("efekBocorPipa")
            .setAttribute("visible", "true");
        else if (jenisRisikoAktif === "Korsleting Listrik")
          document
            .getElementById("efekListrik")
            .setAttribute("visible", "true");
        else {
          document.getElementById("efekHancur").setAttribute("visible", "true");
          // Trigger camera shake untuk gempa bumi
          if (jenisRisikoAktif === "Evakuasi Gempa Bumi") {
            shakeCamera(2000); // Shake selama 2 detik
          }
        }
      } catch (e) {
        console.log("Gagal memunculkan efek visual 3D.");
      }

      let sisaWaktu = 5;
      let textTimer = document.getElementById("timerText");
      textTimer.innerHTML = "WAKTU: " + sisaWaktu + "s";
      textTimer.style.display = "block";

      countdownInterval = setInterval(() => {
        sisaWaktu--;
        textTimer.innerHTML = "WAKTU: " + sisaWaktu + "s";

        if (sisaWaktu <= 0) {
          clearInterval(countdownInterval);
          textTimer.style.display = "none";
          autoFailTimeout(); // Auto-fail dengan simpan ke database
        }
      }, 1000);
    }
  }, jeda);
}

function ambilTindakan() {
  if (!isRunning || jenisRisikoAktif === "") {
    isRunning = true;
    let acakSkenario =
      skenarioK3[Math.floor(Math.random() * skenarioK3.length)];
    jenisRisikoAktif = acakSkenario.jenis_risiko;
  }

  clearInterval(countdownInterval);
  try {
    document.getElementById("timerText").style.display = "none";
  } catch (e) {}

  isRunning = false;

  let detailSkenario = skenarioK3.find(
    (s) => s.jenis_risiko === jenisRisikoAktif,
  );
  if (!detailSkenario) detailSkenario = skenarioK3[0];

  let daftarPilihan = detailSkenario.pilihan;
  let teksPrompt = `⚠️ BAHAYA ${jenisRisikoAktif.toUpperCase()} TERDETEKSI! ⚠️\n\n${detailSkenario.soal}\n\nPilih tindakan yang paling tepat:\n\n`;

  daftarPilihan.forEach((pil, index) => {
    teksPrompt += `${index + 1}. ${pil.teks}\n`;
  });

  teksPrompt += `\nKetik angka pilihan Anda (1, 2, 3, atau 4):`;

  let inputUser = prompt(teksPrompt);
  let indexTerpilih = parseInt(inputUser) - 1;

  if (isNaN(indexTerpilih) || indexTerpilih < 0 || indexTerpilih > 3) {
    alert("Pilihan tidak valid! Simulasi dibatalkan.");
    window.location.reload();
    return;
  }

  let hasilPilihan = daftarPilihan[indexTerpilih];
  let idPeserta = document.getElementById("pilihPeserta").value;

  // FITUR METRIK DOSEN: Menentukan Kategori Risiko
  let kategoriRisiko = "Tinggi";
  if (hasilPilihan.skor === 100) kategoriRisiko = "Rendah";
  else if (hasilPilihan.skor >= 50) kategoriRisiko = "Sedang";

  // FITUR NOVELTY: Modul Remedial AI jika jawaban salah
  let modulRemedial = "";
  if (hasilPilihan.skor < 70) {
    modulRemedial = `\n\n📚 [MODUL PEMBELAJARAN REMEDIAL AI]:\nAnda gagal dalam simulasi ini. Pelajari kembali SOP penanganan '${jenisRisikoAktif}'. Selalu utamakan keselamatan diri terlebih dahulu dan pahami penggunaan APAR/Spill Kit yang sesuai dengan SOP K3.`;
  }

  // AJAX Mengirim data nilai simulasi ke database
  let formData = new FormData();
  formData.append("id_user", idPeserta);
  formData.append("jenis_risiko", jenisRisikoAktif);
  formData.append("skor", hasilPilihan.skor);
  formData.append(
    "status_kelulusan",
    hasilPilihan.skor >= 70 ? "LULUS" : "GAGAL",
  );
  formData.append("rekomendasi", hasilPilihan.rekomendasi);
  formData.append("konsekuensi", hasilPilihan.konsekuensi);
  formData.append("tindakan_dipilih", hasilPilihan.teks); // Tambahan untuk decision log
  formData.append("kategori_risiko", kategoriRisiko); // Baru untuk metrik dosen

  fetch("../backend/api/simulation.php?action=save_result", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "sukses") {
        if (hasilPilihan.skor >= 70) {
          // Lulus - tampilkan alert sederhana
          let pesan = `✅ LULUS SIMULASI\n${"=".repeat(30)}\n`;
          pesan += `🎯 Skor: ${hasilPilihan.skor}/100\n`;
          pesan += `📊 Kategori Risiko: ${kategoriRisiko}\n\n`;
          pesan += `💡 Rekomendasi: ${hasilPilihan.rekomendasi}\n\n`;
          pesan += `🎉 Selamat! Anda telah menguasai penanganan ${jenisRisikoAktif}`;

          alert(pesan);
          window.location.reload();
        } else {
          // Gagal - tampilkan modal remedial personalized
          showRemedialModal({
            jenisRisiko: jenisRisikoAktif,
            skor: hasilPilihan.skor,
            kategoriRisiko: kategoriRisiko,
            konsekuensi: hasilPilihan.konsekuensi,
            rekomendasi: hasilPilihan.rekomendasi,
            tindakanDipilih: hasilPilihan.teks,
            alasanGagal: "Tindakan yang dipilih tidak sesuai prosedur K3",
          });
        }
      } else {
        alert("Gagal menyimpan data: " + data.pesan);
        window.location.reload();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan koneksi ke server!");
      window.location.reload();
    });
}

// Load Chart.js untuk grafik
const ctx = document.getElementById("grafikK3").getContext("2d");
const chartK3 = new Chart(ctx, {
  type: "doughnut",
  data: {
    labels: ["Lulus", "Gagal"],
    datasets: [
      {
        data: [0, 0], // Will be updated via API
        backgroundColor: ["#28a745", "#dc3545"],
        borderWidth: 0,
      },
    ],
  },
  options: {
    cutout: "70%",
    plugins: { legend: { display: false } },
  },
});

// Initialize chart with data from data attributes
function initializeChart() {
  const lulus = parseInt(document.body.getAttribute("data-stats-lulus")) || 0;
  const gagal = parseInt(document.body.getAttribute("data-stats-gagal")) || 0;

  chartK3.data.datasets[0].data = [lulus, gagal];
  chartK3.update();

  updateChartSummary(lulus, gagal);
}

// Update chart summary text
function updateChartSummary(lulus, gagal) {
  const total = lulus + gagal;
  const summaryEl = document.getElementById("chartStatSummary");

  if (total === 0) {
    summaryEl.textContent = "Belum ada data simulasi";
  } else {
    const lulusPercent = Math.round((lulus / total) * 100);
    const gagalPercent = Math.round((gagal / total) * 100);
    summaryEl.textContent = `Lulus: ${lulus} (${lulusPercent}%) | Gagal: ${gagal} (${gagalPercent}%)`;
  }
}

async function refreshStatistikKelulusan() {
  try {
    const response = await fetch("../backend/api/simulation.php?action=stats");
    if (!response.ok) throw new Error("Gagal memuat statistik");

    const stats = await response.json();
    const lulus = parseInt(stats.lulus) || 0;
    const gagal = parseInt(stats.gagal) || 0;
    const total = parseInt(stats.total) || 0;

    chartK3.data.datasets[0].data = [lulus, gagal];
    chartK3.update();

    updateChartSummary(lulus, gagal);
  } catch (err) {
    console.error(err);
    const summary = document.getElementById("chartStatSummary");
    if (summary) {
      summary.textContent = "Gagal memuat statistik kelulusan.";
    }
  }
}

refreshStatistikKelulusan();

// Remedial Modal Functions
function showRemedialModal(data) {
  remedialData = data;
  const modal = document.getElementById("remedialModal");
  const title = document.getElementById("remedialTitle");
  const analysis = document.getElementById("errorAnalysis");
  const steps = document.getElementById("correctionSteps");
  const resources = document.getElementById("learningResources");

  // Set title
  title.innerHTML = `📚 PEMBELAJARAN REMEDIAL: ${data.jenisRisiko}`;

  // Set analysis
  analysis.innerHTML = generateErrorAnalysis(data);

  // Set correction steps
  steps.innerHTML = generateCorrectionSteps(data);

  // Set learning resources
  resources.innerHTML = generateLearningResources(data);

  // Setup 3D visualization
  setupRemedialVisualization(data);

  // Show modal
  modal.style.display = "flex";
  document.body.style.overflow = "hidden";
}

function closeRemedialModal() {
  const modal = document.getElementById("remedialModal");
  modal.style.display = "none";
  document.body.style.overflow = "auto";

  // Reset 3D scene
  const scene = document.getElementById("remedialScene");
  if (scene) {
    const visualization = document.getElementById("remedialVisualization");
    if (visualization) {
      visualization.innerHTML = "";
    }
  }
}

function retrySimulation() {
  closeRemedialModal();

  // Langsung mulai simulasi pada skenario yang sama
  startSim(remedialData.jenisRisiko);
}

function generateErrorAnalysis(data) {
  let analysis = "";

  switch (data.jenisRisiko) {
    case "Kebocoran Pipa Gas":
      if (
        data.tindakanDipilih.includes("memperbaiki") ||
        data.tindakanDipilih.includes("langsung")
      ) {
        analysis = `
                    <p><strong>Kesalahan Utama:</strong> Anda mencoba memperbaiki pipa bocor secara langsung tanpa APD yang memadai.</p>
                    <p><strong>Akibatnya:</strong> Terpapar gas berbahaya yang dapat menyebabkan keracunan, kebakaran, atau ledakan.</p>
                    <p><strong>Analisis:</strong> Prioritas utama dalam K3 adalah keselamatan diri. Tindakan perbaikan harus dilakukan oleh tim teknis yang terlatih dengan peralatan yang sesuai.</p>
                `;
      } else if (data.skor === 0) {
        analysis = `
                    <p><strong>Kesalahan Utama:</strong> Waktu habis tanpa tindakan pencegahan yang tepat.</p>
                    <p><strong>Akibatnya:</strong> Gas bocor terus menyebar, membahayakan area kerja dan lingkungan sekitar.</p>
                    <p><strong>Analisis:</strong> Respon cepat dalam situasi darurat K3 sangat krusial. Setiap detik berharga untuk mencegah eskalasi bahaya.</p>
                `;
      }
      break;

    case "Korsleting Listrik":
      if (data.tindakanDipilih.includes("air")) {
        analysis = `
                    <p><strong>Kesalahan Utama:</strong> Menggunakan air untuk memadamkan kebakaran listrik.</p>
                    <p><strong>Akibatnya:</strong> Air adalah konduktor listrik yang dapat memperburuk korsleting dan menyebabkan sengatan listrik fatal.</p>
                    <p><strong>Analisis:</strong> Setiap jenis kebakaran membutuhkan jenis pemadam yang sesuai. Kebakaran listrik harus menggunakan APAR CO2 atau bahan non-konduktif.</p>
                `;
      }
      break;

    case "Tumpahan Oli":
      analysis = `
                <p><strong>Kesalahan Utama:</strong> Penanganan tumpahan oli tidak sesuai prosedur K3.</p>
                <p><strong>Akibatnya:</strong> Resiko tergelincir, pencemaran lingkungan, dan bahaya kebakaran.</p>
                <p><strong>Analisis:</strong> Tumpahan bahan kimia berbahaya memerlukan penanganan khusus dengan peralatan Spill Kit yang sesuai.</p>
            `;
      break;

    default:
      analysis = `
                <p><strong>Kesalahan Utama:</strong> Tindakan tidak sesuai dengan prosedur K3 yang benar.</p>
                <p><strong>Akibatnya:</strong> Membahayakan keselamatan diri dan orang lain di sekitar area kerja.</p>
                <p><strong>Analisis:</strong> Simulasi ini menunjukkan perlunya pemahaman mendalam tentang SOP K3 di lingkungan kerja industri.</p>
            `;
  }

  return analysis;
}

function generateCorrectionSteps(data) {
  let steps = "<ol>";

  switch (data.jenisRisiko) {
    case "Kebocoran Pipa Gas":
      steps += `
                <li>Evakuasi area segera dan aktifkan alarm darurat</li>
                <li>Hubungi tim teknis/DAMKAR untuk penanganan profesional</li>
                <li>Gunakan APD lengkap jika harus berada di area terdampak</li>
                <li>Matikan sumber listrik di area tersebut jika memungkinkan</li>
                <li>Tunggu tim ahli untuk perbaikan sistem pipa</li>
            `;
      break;

    case "Korsleting Listrik":
      steps += `
                <li>Matikan sumber listrik dari MCB utama</li>
                <li>Jangan sentuh peralatan listrik yang rusak</li>
                <li>Gunakan APAR CO2 untuk memadamkan kebakaran listrik</li>
                <li>Evakuasi area dan hubungi tim teknis</li>
                <li>Laporkan insiden ke supervisor K3</li>
            `;
      break;

    case "Tumpahan Oli":
      steps += `
                <li>Kurung area tumpahan dengan safety cone</li>
                <li>Gunakan Spill Kit untuk menyerap oli</li>
                <li>Jangan biarkan oli mengalir ke saluran pembuangan</li>
                <li>Laporkan ke tim lingkungan untuk disposal yang benar</li>
                <li>Bersihkan area dengan detergent yang sesuai</li>
            `;
      break;

    default:
      steps += `
                <li>Evaluasi situasi bahaya dengan cepat</li>
                <li>Prioritaskan keselamatan diri dan rekan kerja</li>
                <li>Aktifkan prosedur evakuasi jika diperlukan</li>
                <li>Hubungi tim darurat sesuai jenis insiden</li>
                <li>Dokumentasikan kejadian untuk pelaporan</li>
            `;
  }

  steps += "</ol>";
  return steps;
}

function generateLearningResources(data) {
  let resources = "<ul>";

  switch (data.jenisRisiko) {
    case "Kebocoran Pipa Gas":
      resources += `
                <li><strong>SOP Penanganan Kebocoran Gas:</strong> Panduan identifikasi dan penanganan gas berbahaya</li>
                <li><strong>Pelatihan APD:</strong> Penggunaan alat pelindung diri untuk bahaya kimia</li>
                <li><strong>Sistem Ventilasi Darurat:</strong> Cara kerja dan penggunaan sistem ventilasi</li>
                <li><strong>Komunikasi Darurat:</strong> Prosedur pelaporan dan koordinasi tim</li>
            `;
      break;

    case "Korsleting Listrik":
      resources += `
                <li><strong>Klasifikasi Kebakaran:</strong> Pemahaman jenis kebakaran dan APAR yang sesuai</li>
                <li><strong>Sistem Kelistrikan:</strong> Pengetahuan dasar sistem listrik industri</li>
                <li><strong>P3K Listrik:</strong> Penanganan korban sengatan listrik</li>
                <li><strong>Maintenance Preventif:</strong> Pemeriksaan rutin peralatan listrik</li>
            `;
      break;

    case "Tumpahan Oli":
      resources += `
                <li><strong>Spill Response:</strong> Teknik penanganan tumpahan bahan kimia</li>
                <li><strong>Environmental Protection:</strong> Dampak pencemaran dan pencegahan</li>
                <li><strong>Material Safety Data Sheet:</strong> Informasi keamanan bahan kimia</li>
                <li><strong>Cleanup Procedures:</strong> Prosedur pembersihan yang aman</li>
            `;
      break;

    default:
      resources += `
                <li><strong>SOP K3 Umum:</strong> Standar operasional prosedur keselamatan</li>
                <li><strong>Risk Assessment:</strong> Teknik identifikasi dan evaluasi risiko</li>
                <li><strong>Emergency Response:</strong> Tanggap darurat berbagai jenis insiden</li>
                <li><strong>Safety Training:</strong> Pelatihan keselamatan kerja berkala</li>
            `;
  }

  resources += "</ul>";
  return resources;
}

// Initialize chart when page loads
document.addEventListener("DOMContentLoaded", function () {
  initializeChart();
});

function setupRemedialVisualization(data) {
  const visualization = document.getElementById("remedialVisualization");
  const remedialText = document.getElementById("remedialText");

  // Clear previous content
  visualization.innerHTML = "";

  switch (data.jenisRisiko) {
    case "Kebocoran Pipa Gas":
      // Visualisasi pipa bocor dengan warning
      visualization.innerHTML = `
                <a-box width="0.8" height="0.8" depth="0.8" color="#2c3e50" position="0 0.4 -3"></a-box>
                <a-cylinder radius="0.06" height="1.2" color="#7f8c8d" position="0 1.2 -3"></a-cylinder>
                <a-cylinder radius="0.06" height="3" color="#7f8c8d" position="1.5 1.8 -3" rotation="0 0 90"></a-cylinder>
                <a-sphere radius="0.3" color="#e74c3c" position="1.5 1.8 -3" opacity="0.9" animation="property: scale; from: 1 1 1; to: 1.2 1.2 1.2; dur: 1000; loop: true; dir: alternate;"></a-sphere>
                <a-text value="❌ BAHAYA!" position="0 2.5 -2" align="center" color="#dc3545" scale="0.6 0.6 0.6"></a-text>
                <a-text value="Gas Bocor" position="0 2.2 -2" align="center" color="#007bff" scale="0.5 0.5 0.5"></a-text>
            `;
      remedialText.setAttribute("value", "Area berbahaya - Evakuasi segera!");
      break;

    case "Korsleting Listrik":
      // Visualisasi korsleting dengan percikan api
      visualization.innerHTML = `
                <a-box width="1" height="0.6" depth="0.4" color="#34495e" position="0 0.3 -3"></a-box>
                <a-cylinder radius="0.03" height="0.8" color="#f39c12" position="-0.2 0.8 -3" rotation="15 0 0"></a-cylinder>
                <a-cylinder radius="0.03" height="0.8" color="#f39c12" position="0.2 0.8 -3" rotation="-15 0 0"></a-cylinder>
                <a-sphere radius="0.15" color="#e74c3c" position="0 0.6 -2.8" opacity="0.8" animation="property: scale; from: 1 1 1; to: 1.3 1.3 1.3; dur: 800; loop: true; dir: alternate;"></a-sphere>
                <a-sphere radius="0.1" color="#ffaa00" position="-0.1 0.7 -2.9" opacity="0.9" animation="property: scale; from: 1 1 1; to: 1.4 1.4 1.4; dur: 600; loop: true; dir: alternate;"></a-sphere>
                <a-text value="⚡ KORLET!" position="0 2.5 -2" align="center" color="#dc3545" scale="0.6 0.6 0.6"></a-text>
                <a-text value="JANGAN pakai air!" position="0 2.2 -2" align="center" color="#007bff" scale="0.5 0.5 0.5"></a-text>
            `;
      remedialText.setAttribute("value", "Gunakan APAR CO2, bukan air!");
      break;

    case "Tumpahan Oli":
      // Visualisasi genangan oli
      visualization.innerHTML = `
                <a-plane position="0 0.01 -3" rotation="-90 0 0" width="4" height="4" color="#2c3e50" opacity="0.8"></a-plane>
                <a-circle radius="1.5" color="#f1c40f" position="0 0.02 -3" rotation="-90 0 0" opacity="0.7" animation="property: scale; from: 1 1 1; to: 1.1 1.1 1.1; dur: 1200; loop: true; dir: alternate;"></a-circle>
                <a-cylinder radius="0.1" height="0.05" color="#34495e" position="0.5 0.1 -2.5"></a-cylinder>
                <a-cylinder radius="0.08" height="0.03" color="#e74c3c" position="-0.3 0.08 -2.8" rotation="90 0 0"></a-cylinder>
                <a-text value="🛢️ TUMPAHAN" position="0 2.5 -2" align="center" color="#dc3545" scale="0.6 0.6 0.6"></a-text>
                <a-text value="Bahaya Licin!" position="0 2.2 -2" align="center" color="#007bff" scale="0.5 0.5 0.5"></a-text>
            `;
      remedialText.setAttribute("value", "Gunakan Spill Kit untuk cleanup!");
      break;

    default:
      // Visualisasi umum bahaya
      visualization.innerHTML = `
                <a-sphere radius="0.8" color="#e74c3c" position="0 1 -3" opacity="0.8" animation="property: scale; from: 1 1 1; to: 1.2 1.2 1.2; dur: 1000; loop: true; dir: alternate;"></a-sphere>
                <a-text value="⚠️ BAHAYA!" position="0 2.5 -2" align="center" color="#dc3545" scale="0.8 0.8 0.8"></a-text>
                <a-text value="Ikuti SOP K3" position="0 2.2 -2" align="center" color="#007bff" scale="0.6 0.6 0.6"></a-text>
            `;
      remedialText.setAttribute("value", "Selalu prioritaskan keselamatan!");
  }
}
