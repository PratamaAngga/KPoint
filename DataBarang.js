function tampilkanDataBarang() {
  const mainContent = document.getElementById("main-content");
  mainContent.innerHTML = "";

  // Contoh data
  const dataBarang = [
    { nama: "Barang 1", deskripsi: "Deskripsi 1" },
    { nama: "Barang 2", deskripsi: "Deskripsi 2" },
    // dst...
  ];

  const grid = document.createElement("div");
  grid.className = "grid";

  dataBarang.forEach(item => {
    const card = document.createElement("div");
    card.className = "card";
    card.innerHTML = `<h3>${item.nama}</h3><p>${item.deskripsi}</p>`;
    grid.appendChild(card);
  });

  mainContent.appendChild(grid);
}

window.onload = tampilkanDataBarang;


// function tampilkanDataBarang() {
//   const template = document.getElementById("data-barang-page");
//   const mainContent = document.getElementById("main-content");

//   mainContent.innerHTML = "";
//   const clone = template.content.cloneNode(true);
//   mainContent.appendChild(clone);
// }

// window.onload = () => {
//   tampilkanDataBarang();
// };
