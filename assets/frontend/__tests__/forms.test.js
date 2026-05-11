/* eslint-env jest */

describe('forms.js trigger parsing', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
        delete window.__RSFM_FORMS_TEST__;
        jest.resetModules();
        require('../forms.js');
    });

    test('parses fragment trigger correctly', () => {
        expect(window.__RSFM_FORMS_TEST__.parseTriggerToFormId('#restatify-form-kontaktformular')).toBe('kontaktformular');
    });

    test('parses absolute URL with hash correctly', () => {
        expect(window.__RSFM_FORMS_TEST__.parseTriggerToFormId('http://localhost/restatify.tech/#restatify-form-kontaktformular')).toBe('kontaktformular');
    });

    test('ignores booking trigger prefix', () => {
        expect(window.__RSFM_FORMS_TEST__.parseTriggerToFormId('#restatify-booking')).toBe('');
    });

    test('ignores empty values', () => {
        expect(window.__RSFM_FORMS_TEST__.parseTriggerToFormId('')).toBe('');
        expect(window.__RSFM_FORMS_TEST__.parseTriggerToFormId('   ')).toBe('');
    });
});
