/**
 * Controller: ascultă butoanele, apelează modelul, actualizează view-ul.
 */
class CalculatorController {
  constructor(model, view) {
    this._model = model;
    this._view = view;
    this._root = document.getElementById("calculator");
    this._bind();
    this._syncView();
  }

  _syncView() {
    this._view.update(this._model.getState());
  }

  _bind() {
    this._root.addEventListener("click", (event) => {
      const button = event.target.closest("button[data-action]");
      if (!button) {
        return;
      }
      const { action } = button.dataset;
      switch (action) {
        case "digit":
          this._model.digit(button.dataset.value);
          break;
        case "operator":
          this._model.setOperator(button.dataset.value);
          break;
        case "equals":
          this._model.equals();
          break;
        case "clear":
          this._model.clear();
          break;
        default:
          break;
      }
      this._syncView();
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const model = new CalculatorModel();
  const view = new CalculatorView();
  new CalculatorController(model, view);
});
