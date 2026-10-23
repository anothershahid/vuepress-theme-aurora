(function () {
  const fields = document.querySelectorAll('input[maxlength],textarea[maxlength]');
  fields.forEach((field) => {
    field.addEventListener('input', () => {
      if (field.value.length > Number(field.maxLength)) {
        field.value = field.value.slice(0, Number(field.maxLength));
      }
    });
  });
})();
