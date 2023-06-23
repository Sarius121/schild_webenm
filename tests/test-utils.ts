import { expect, Page } from '@playwright/test';
import testConfig from "./test-config.json";

export async function login(page: Page, checkLogin: boolean = true, finishLogin: boolean = true) {
    var user = testConfig.testUser;
    var pwd = testConfig.testPwd;

    await page.locator('#login-box form input[name="username"]').fill(user);
    await page.locator('#login-box form input[name="password"]').fill(pwd);
    await page.locator('#login-box form input[name="password"]').press("Enter");

    await page.waitForLoadState('networkidle');
    
    if (!finishLogin) {
        return;
    }
    const contents = await page.locator('h2').allTextContents();
    if (contents.includes("Ungesicherte Änderungen wiederherstellen?")) {
        await page.locator('input[value="Nein"]').click();
    }
    await page.waitForLoadState('networkidle');

    if (checkLogin) {
        //expect(page.locator("#home-container #header .col-sm-auto >> nth=1")).toContainText(user);
        expectLoginAs(page, user);
    }
}

export function expectLoginAs(page: Page, user: string) {
    expect(page.locator("text=Angemeldet als")).toContainText(user);
}

export async function logout(page: Page) {
    await page.click("#home-container #header .col-sm-auto .btn");

    await page.waitForLoadState('load');

    expect(page.locator("#login-box")).toBeDefined();
}

export class DataTableCell
{
    private page: Page;
    private tableid: string;
    private row: number;
    private col: string;
    private selector: string;

    constructor(page: Page, tableid: string, row: number, col: string) {
        this.page = page;
        this.tableid = tableid;
        this.row = row;
        this.col = col;
        this.selector = "#" + this.tableid + "_" + this.row.toFixed(0) + " .editablegrid-" + this.col;
    }

    insertValue(value: string) {
        return new Promise<void>(async (resolve) => {
            await this.page.locator(this.selector).scrollIntoViewIfNeeded();
            await this.page.locator(this.selector).click();
            await this.page.locator(this.selector + " input").fill(value);
            await this.page.locator(this.selector + " input").press("Enter");
            var gotValue = await this.page.locator(this.selector).innerText();
            await expect(gotValue).toBe(value);

            // check if the new value is correctly inserted into the database by reloading
            await this.page.reload();
            gotValue = await this.getValue();
            await expect(gotValue).toBe(value);
            resolve();
        });
    }

    getValue() {
        return new Promise<string>(async (resolve) => {
            var res = await this.page.locator(this.selector).innerText();
            resolve(res);
        });
    }

    setPage(page: Page) {
        this.page = page;
    }
}

export async function getRandomRow(page: Page, tableid: string, minRow: number = 0) {
    var row_count = (await page.locator("#" + tableid + " tbody tr").count()).valueOf();
    return Math.random() * (row_count - 1 - minRow) + minRow;
}

export async function getRandomGrade(page: Page) {
    // -1 because first row is header
    var count = (await page.locator("#grades-list .row:not(.hidden)").count() - 1).valueOf();
    var row = Math.random() * (count - 1) + 1;
    var grade = await page.locator("#grades-list .row:not(.hidden) >> nth=" + row.toFixed(0)).locator(".col-sm-1 >> nth=1").innerText();
    return grade;
}

export async function insertValueIntoTable(page: Page, tableid: string, row: number, col: string, value: string) {
    var cell = new DataTableCell(page, tableid, row, col);
    await cell.insertValue(value);
}

export function selectMenuItem(page: Page, tab: string, item: string) {
    page.locator("#top-box .tab-layout-2").getByText(tab).click();
    page.locator("#top-box .tab-content").getByText(item).click();
}