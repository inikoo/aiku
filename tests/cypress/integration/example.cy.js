describe('Login Test', () => {
    it('shows login', () => {
        cy.visit('/login');
        cy.get('button').contains('Sign in').click()

    });
});
