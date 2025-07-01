const { isValidPassword } = require('./passwordValidator');

describe('isValidPassword', () => {
  test('accepte un mot de passe valide', () => {
    expect(isValidPassword('Motdepassevalide1!')).toBe(true);
  });

  test('rejette un mot de passe trop court', () => {
    expect(isValidPassword('A!')).toBe(false);
  });

  test('rejette un mot de passe sans majuscule', () => {
    expect(isValidPassword('motdepassevalide1')).toBe(false);
  });

  test('rejette un mot de passe sans minuscule', () => {
    expect(isValidPassword('MOTDEPASSE1!')).toBe(false);
  });

  test('rejette un mot de passe sans chiffre', () => {
    expect(isValidPassword('Motdepasse!')).toBe(false);
  });

  test('rejette un mot de passe sans caractère spécial', () => {
    expect(isValidPassword('Motdepasse1')).toBe(false);
  });
});
