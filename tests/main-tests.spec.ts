import { test, expect, Page } from '@playwright/test';
import { login, logout, DataTableCell, getRandomRow, getRandomGrade, insertValueIntoTable } from "./test-utils";

test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/webenm');

    await login(page);
});

test.afterEach(async ({ page }) => {
    await logout(page);
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
        // make sure that only valid grades are returned by getRandomGrade
        await page.dblclick("#ExamsTable_0 .editablegrid-Vornote");
        await page.click('#grades-modal :text("OK")');
    });

    test("Vornote-input", async ({ page }) => {
        var row = await getRandomRow(page, "examsTable");
        var grade = await getRandomGrade(page);

        await insertValueIntoTable(page, "ExamsTable", row, "Vornote", grade);
    });

    test("NoteSchriftlich-input", async ({ page }) => {
        var row = await getRandomRow(page, "examsTable");
        var grade = await getRandomGrade(page);

        await insertValueIntoTable(page, "ExamsTable", row, "NoteSchriftlich", grade);
    });
});