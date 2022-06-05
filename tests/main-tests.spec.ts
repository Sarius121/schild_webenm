import { test, expect, Page } from '@playwright/test';

test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/webenm');

    await login(page);
});

test.afterEach(async ({ page }) => {
    await logout(page);
});

test.describe('auth', () => {
    test('login-logout', async ({ page }) => {
        // everything important is done in before all and after all
    });

});

test.describe('data.grades', () => {
    test('grade-input', async ({ page }) => {
        var row = await getRandomRow(page, "gradeTable");
        var grade = await getRandomGrade(page);

        await insertValueIntoTable(page, "GradeTable", row, "NotenKrz", grade);
    });

    test('FS-input', async ({ page }) => {
        var row = await getRandomRow(page, "gradeTable");
        var value = (Math.random() * 10).toFixed(0);

        await insertValueIntoTable(page, "GradeTable", row, "Fehlstd", value);
    });

    test('uFS-input', async ({ page }) => {
        var row = await getRandomRow(page, "gradeTable");
        var value = (Math.random() * 10).toFixed(0);

        await insertValueIntoTable(page, "GradeTable", row, "uFehlstd", value);
    });

    test("Mahnung-input", async ({ page }) => {
        // TODO
    });

    test('arrow-up-navigation', async ({ page }) => {
        var row = await getRandomRow(page, "gradeTable", 1);

        await page.locator("#GradeTable_" + row.toFixed(0) + " .editablegrid-NotenKrz").scrollIntoViewIfNeeded();
        await page.locator("#GradeTable_" + row.toFixed(0) + " .editablegrid-NotenKrz").click({noWaitAfter: true});
        await page.locator("#GradeTable_" + row.toFixed(0) + " .editablegrid-NotenKrz input").press("ArrowUp", {noWaitAfter: true});
        await expect(page.locator("#GradeTable_" + (row - 1).toFixed(0) + " .editablegrid-NotenKrz input")).toBeFocused();
    });
});

test.describe('data.class-teacher', () => {
    test.beforeEach(async ({ page }) => {
        await page.click("#tab-class-teacher");
    });

    test("FS-input", async ({ page }) => {
        var row = await getRandomRow(page, "classTeacherTable");
        var value = (Math.random() * 10).toFixed(0);

        await insertValueIntoTable(page, "ClassTeacherTable", row, "SumFehlstd", value);
    });

    test("uFS-input", async ({ page }) => {
        var row = await getRandomRow(page, "classTeacherTable");
        var value = (Math.random() * 10).toFixed(0);

        await insertValueIntoTable(page, "ClassTeacherTable", row, "SumFehlstdU", value);
    });
});

test.describe('data.exams', () => {
    test.beforeEach(async ({ page }) => {
        await page.click("#tab-exams");
    });

    test("FS-input", async ({ page }) => {
        var row = await getRandomRow(page, "classTeacherTable");
        var value = (Math.random() * 10).toFixed(0);

        await insertValueIntoTable(page, "ClassTeacherTable", row, "SumFehlstd", value);
    });

    test("uFS-input", async ({ page }) => {
        var row = await getRandomRow(page, "classTeacherTable");
        var value = (Math.random() * 10).toFixed(0);

        await insertValueIntoTable(page, "ClassTeacherTable", row, "SumFehlstdU", value);
    });
});

async function login(page: Page) {
    var user = "***username***";
    var pwd = "***password***";

    await page.locator('#login-box form input[name="username"]').fill(user);
    await page.locator('#login-box form input[name="password"]').fill(pwd);
    await page.locator('#login-box form input[name="password"]').press("Enter");

    await page.waitForLoadState('networkidle');

    await expect(page.locator("#home-container #header .col-sm-auto >> nth=1")).toContainText(user);
}

async function logout(page: Page) {
    await page.click("#home-container #header .col-sm-auto .btn");

    await page.waitForLoadState('load');

    await expect(page.locator("#login-box")).toBeDefined();
}

async function getRandomRow(page: Page, tableid: string, minRow: number = 0) {
    var row_count = (await page.locator("#" + tableid + " tbody tr").count()).valueOf();
    return Math.random() * (row_count - 1 - minRow) + minRow;
}

async function getRandomGrade(page: Page) {
    // -1 because first row is header
    var count = (await page.locator("#grades-list .row").count() - 1).valueOf();
    var row = Math.random() * (count - 1) + 1;
    var grade = await page.locator("#grades-list .row >> nth=" + row.toFixed(0)).locator(".col-sm-1 >> nth=1").innerText();
    return grade;
}

async function insertValueIntoTable(page: Page, tableid: string, row: number, col: string, value: string) {
    var selector = "#" + tableid + "_" + row.toFixed(0) + " .editablegrid-" + col;
    await page.locator(selector).scrollIntoViewIfNeeded();
    await page.locator(selector).click();
    await page.locator(selector + " input").fill(value);
    await page.locator(selector + " input").press("Enter");
    var gotValue = await page.locator(selector).innerText();
    await expect(gotValue).toBe(value);

    // check if the new value is correctly inserted into the database by reloading
    await page.reload();
    gotValue = await page.locator(selector).innerText();
    await expect(gotValue).toBe(value);
}