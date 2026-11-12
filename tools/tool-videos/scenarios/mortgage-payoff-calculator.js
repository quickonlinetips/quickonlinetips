// Scenario: Mortgage Payoff Calculator (page 70337)
module.exports = {
  slug: 'mortgage-payoff-calculator',
  url: 'https://www.quickonlinetips.com/archives/tools/mortgage-payoff-calculator/',
  intro: {
    title: 'Mortgage Payoff Calculator',
    subtitle: 'Pay off your mortgage early with extra payments',
    options: ['Extra monthly payments', 'Yearly bonus payments', 'Pay off by a target date', 'Year-by-year table'],
  },
  async run(h) {
    await h.intro(this.intro);
    await h.goto(this.url);

    await h.caption('Step 1: enter the mortgage as it is today');
    await h.type('#qmp-bal', '250,000');
    await h.type('#qmp-rate', '6.5');
    await h.type('#qmp-ty', '25');
    await h.moveTo('#qmp-pmt'); await h.wait(1300);

    await h.caption('Step 2: add $200 extra every month');
    await h.type('#qmp-xm', '200');
    await h.caption('Result: 5+ years sooner, about $65,000 interest saved');
    await h.moveTo('#qmp-big'); await h.wait(2400);
    await h.moveTo('#qmp-s-save'); await h.wait(1500);

    await h.caption('Year-by-year balance table');
    await h.wheel('.qmp-tblwrap', 120, 3); await h.wait(800);

    await h.caption('Add a yearly bonus payment too');
    await h.type('#qmp-xy', '1,000');
    await h.moveTo('#qmp-big'); await h.wait(2200);

    await h.caption('Option 2: be mortgage-free in 15 years');
    await h.click('#qmp-tabs button[data-t=goal]'); await h.wait(400);
    await h.type('#qmp-gy', '15');
    await h.moveTo('#qmp-big'); await h.wait(2400);

    await h.caption('Copy the answer');
    await h.click('#qmp-copy');
    await h.moveTo('#qmp-status'); await h.wait(1600);

    await h.caption('Start over clears every field');
    await h.click('#qmp-reset'); await h.wait(1200);
    await h.caption('');

    await h.outro();
  },
};
