/**
 * View: actualizează afișajul expresiei și al rezultatului (fără logică de calcul).
 */
class CalculatorView {
  constructor(rootSelector) {
    const root = rootSelector ? document.querySelector(rootSelector) : document;
    this._expressionEl = root.querySelector("#expression");
    this._resultEl = root.querySelector("#result");
  }

  /**
   * @param {{ expression: string, display: string, hasError: boolean }} state
   */
  update(state) {
    const expr = state.expression && state.expression.length > 0 ? state.expression : "\u00A0";
    this._expressionEl.textContent = expr;
    this._resultEl.textContent = state.display;
    this._resultEl.classList.toggle("calculator__result--error", state.hasError);
  }
}
