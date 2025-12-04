document.addEventListener("DOMContentLoaded", function () {

    // Flatpickr **direkt** an das Inputfeld binden
    const picker = flatpickr("#openTimeField", {
        enableTime: true,
        time_24hr: true,
        dateFormat: "d/m/Y H:i",
        minDate: new Date(),
        onChange: function(selectedDates, dateStr, instance) {
        instance.input.value = dateStr.trim(); // entfernt Leerzeichen
        }
    });

    // Manuelles Öffnen optional (falls du es brauchst)
    document.querySelector("#openTimeField").addEventListener("click", () => picker.open());
});
