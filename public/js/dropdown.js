function toggleDropdown() {
    document.getElementById("profileDropdown").classList.toggle("show");
}

    // Close dropdown if clicked outside
    window.onclick = function(event) {
      if (!event.target.closest('.dropdown')) {
        document.getElementById("profileDropdown").classList.remove("show");
    }
}