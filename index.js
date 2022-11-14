function addEventlistenertoObjects() {
  let figures = document.querySelectorAll("figure");
  for (let i = 0; i < 32; i++) {
    figures[i].addEventListener("click", function () {
      console.log("Hello");
    });
  }
}
