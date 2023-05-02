describe('Example Test', () => {
    it('shows a homepage', () => {
        cy.visit('/abc');

        cy.contains('Laravel');
    });
});
