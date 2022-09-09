import { test, expect, Page, Browser } from '@playwright/test';
import { login, logout, expectLoginAs, DataTableCell } from "./test-utils";
import testConfig from "./test-config.json";

test.describe("login", () => {

    test.beforeEach(async ({ page }) => {
        await page.goto(testConfig.appURL);
    });

    /**
     * login normally and logout again
     */
    test('normal-login', async ({ page }) => {
        await login(page);
        await logout(page);
    });

    /**
     * login, delete browser session data and login again
     * 
     * this test won't work if inserting values don't work.
     */
    test('not-correctly-logged-out-restore', async ({ page, browser }) => {
        await testNotCorrectlyLoggedOut(page, browser, true);
    });

    /**
     * login, delete browser session data and login again
     * 
     * this test won't work if inserting values don't work.
     */
    test('not-correctly-logged-out-no-restore', async ({ page, browser }) => {
        await testNotCorrectlyLoggedOut(page, browser, false);
    });

});

async function testNotCorrectlyLoggedOut(page: Page, browser: Browser, restoreChanges: boolean) {
    await login(page);
    // make changes
    var cell = new DataTableCell(page, "GradeTable", 1, "Fehlstd");
    var oldValue = await cell.getValue();
    var newValue = String(Number(oldValue) + 1);
    await cell.insertValue(newValue);
    
    // create new context
    const context = await browser.newContext();
    const newPage = await context.newPage();
    await newPage.goto(testConfig.appURL);
    await login(newPage, false, false);

    if (restoreChanges) {
        await newPage.locator('input[value="Ja"]').click();
    } else {
        await newPage.locator('input[value="Nein"]').click();
    }
    await page.waitForLoadState('networkidle');
    expectLoginAs(newPage, testConfig.testUser);

    // check if changes were restored or not
    cell.setPage(newPage);
    if (restoreChanges) {
        expect(await cell.getValue()).toBe(newValue);
    } else {
        expect(await cell.getValue()).toBe(oldValue);
    }

    await logout(newPage);
}