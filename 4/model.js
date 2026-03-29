/**
 * Model: operand1, operand2, operator, displayValue;
 * reguli de calcul și validare (ex.: împărțire la zero).
 */
class CalculatorModel {
  constructor() {
    this.reset();
  }

  reset() {
    this.operand1 = null;
    this.operand2 = null;
    this.operator = null;
    this.displayValue = "0";
    this.expressionText = "";
    this.waitingForOperand = false;
    this.justEvaluated = false;
    this.error = null;
  }

  /**
   * @returns {{ expression: string, display: string, hasError: boolean }}
   */
  getState() {
    return {
      expression: this.expressionText,
      display: this.error || this.displayValue,
      hasError: Boolean(this.error),
    };
  }

  clear() {
    this.reset();
  }

  digit(char) {
    if (this.error) {
      this.reset();
    }
    if (this.justEvaluated) {
      this.displayValue = "0";
      this.expressionText = "";
      this.operand1 = null;
      this.operand2 = null;
      this.operator = null;
      this.justEvaluated = false;
    }
    if (this.waitingForOperand) {
      this.displayValue = "0";
      this.waitingForOperand = false;
    }
    if (this.displayValue === "0") {
      this.displayValue = char;
    } else {
      this.displayValue += char;
    }
    this._syncOperand2FromDisplay();
  }

  _syncOperand2FromDisplay() {
    if (this.operator === null || this.waitingForOperand || this.justEvaluated || this.error) {
      this.operand2 = null;
      return;
    }
    const v = parseFloat(this.displayValue);
    this.operand2 = Number.isFinite(v) ? v : null;
  }

  setOperator(op) {
    if (this.error) {
      return;
    }
    if (this.justEvaluated) {
      this.justEvaluated = false;
      this.operand1 = parseFloat(this.displayValue);
      this.operand2 = null;
      this.operator = op;
      this.expressionText = `${this._formatNumber(this.operand1)} ${this._symbol(op)}`;
      this.waitingForOperand = true;
      return;
    }
    if (this.waitingForOperand && this.operator !== null) {
      this.operator = op;
      this.operand2 = null;
      this.expressionText = `${this._formatNumber(this.operand1)} ${this._symbol(op)}`;
      return;
    }

    const current = parseFloat(this.displayValue);
    if (this.operator !== null && this.operand1 !== null && !this.waitingForOperand) {
      const chained = this._compute(this.operand1, current, this.operator);
      if (chained === null) {
        this._setDivideByZeroError();
        return;
      }
      this.operand1 = chained;
      this.displayValue = this._formatNumber(chained);
      this.operand2 = null;
      this.operator = op;
      this.expressionText = `${this._formatNumber(this.operand1)} ${this._symbol(op)}`;
      this.waitingForOperand = true;
      return;
    }

    this.operand1 = current;
    this.operand2 = null;
    this.operator = op;
    this.expressionText = `${this._formatNumber(this.operand1)} ${this._symbol(op)}`;
    this.waitingForOperand = true;
  }

  equals() {
    if (this.error || this.operator === null || this.waitingForOperand) {
      return;
    }
    const second = parseFloat(this.displayValue);
    const result = this._compute(this.operand1, second, this.operator);
    if (result === null) {
      this._setDivideByZeroError();
      return;
    }
    this.expressionText = `${this._formatNumber(this.operand1)} ${this._symbol(this.operator)} ${this._formatNumber(second)} =`;
    this.displayValue = this._formatNumber(result);
    this.operand1 = null;
    this.operand2 = null;
    this.operator = null;
    this.justEvaluated = true;
  }

  _symbol(op) {
    const map = { "+": "+", "-": "−", "*": "×", "/": "÷" };
    return map[op] || op;
  }

  _formatNumber(n) {
    if (!Number.isFinite(n)) {
      return String(n);
    }
    return Number.isInteger(n) ? String(n) : String(n);
  }

  /**
   * @returns {number | null} null = eroare (împărțire la zero)
   */
  _compute(a, b, op) {
    if (op === "/" && b === 0) {
      return null;
    }
    switch (op) {
      case "+":
        return a + b;
      case "-":
        return a - b;
      case "*":
        return a * b;
      case "/":
        return a / b;
      default:
        return b;
    }
  }

  _setDivideByZeroError() {
    this.error = "Eroare: împărțire la zero";
    this.expressionText = "";
    this.displayValue = "0";
    this.operand1 = null;
    this.operand2 = null;
    this.operator = null;
    this.waitingForOperand = false;
    this.justEvaluated = false;
  }
}
