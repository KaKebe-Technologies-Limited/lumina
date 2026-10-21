<?php
return [
    'slug' => 'machine-learning-introduction',
    'title' => 'Introduction to Machine Learning',
    'category' => 'ai',
    'level' => 'advanced',
    'icon' => 'target',
    'sort' => 360,
    'summary' => 'Learn how machines learn from data: prepare data, train and judge regression, tree, nearest-neighbour and clustering models with scikit-learn, avoid overfitting and bias, and finish a complete project.',
    'description' => '<p>Machine learning (ML) is the part of computing where a program finds patterns in examples instead of following rules that a person wrote by hand. It powers spam filters, crop and weather forecasts, credit scoring for mobile lenders, medical image checks and many other tools that young Africans are already building and using.</p><p>This course teaches the ideas first and the code second. You will learn the main problem types, how to prepare data, how to split it honestly into training and test sets, and how to train linear and logistic regression, decision trees, random forests, k-nearest neighbours and k-means clustering using <strong>scikit-learn</strong>, the standard Python library for classic ML. You will measure models with accuracy, precision, recall, the confusion matrix and error scores, and learn why a model that looks perfect on its own data can still fail in real life.</p><p>The course ends with a full project, a serious look at bias and ethics, and practical ideas for putting a model to work. It assumes you have finished the Data Analysis with Python course and are comfortable with pandas and NumPy. Small examples are worked by hand so you can check every output; where a result depends on randomness or on your library version the lesson tells you so.</p>',
    'outcomes' => [
        'Explain the difference between supervised, unsupervised and other kinds of machine learning and choose the right problem type for a real question.',
        'Prepare data for a model: handle missing values, encode categories, scale numbers and separate features from the label.',
        'Split data into training and test sets and use a baseline to judge whether a model is any good.',
        'Train and use linear regression, logistic regression, decision trees, random forests and k-nearest neighbours with scikit-learn.',
        'Group unlabelled data with k-means clustering and choose a sensible number of clusters.',
        'Evaluate models with accuracy, precision, recall, the confusion matrix, MSE and cross-validation, and recognise overfitting.',
        'Build and report a complete scikit-learn project using a pipeline.',
        'Describe sources of bias, privacy risks and basic ways to save, serve and monitor a trained model.',
    ],
    'requirements' => [
        'Completion of Data Analysis with Python: pandas and Charts, or equivalent pandas and NumPy skills',
        'A computer that can run Python and Jupyter, or a browser and a Google account for Google Colab',
        'School-level maths: averages, graphs of straight lines and percentages',
    ],
    'pass_mark' => 70,
    'resources' => [
        ['title' => 'scikit-learn: official website', 'url' => 'https://scikit-learn.org/'],
        ['title' => 'scikit-learn user guide', 'url' => 'https://scikit-learn.org/stable/user_guide.html'],
        ['title' => 'NumPy: official website', 'url' => 'https://numpy.org/'],
        ['title' => 'pandas: official website', 'url' => 'https://pandas.pydata.org/'],
        ['title' => 'Project Jupyter', 'url' => 'https://jupyter.org/'],
    ],
    'modules' => [
        [
            'title' => 'Module 1: What machine learning is and how to prepare for it',
            'summary' => 'Understand the kinds of machine learning problems, set up scikit-learn, prepare data for a model and learn to split it honestly and set a baseline.',
            'lessons' => [
                [
                    'title' => 'What machine learning is and the main problem types',
                    'minutes' => 15,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to explain what machine learning is, name its main problem types and decide which type fits a real question. This matters because choosing the wrong type of problem at the start wastes weeks of work later.</p>

<h3>Rules versus learning</h3>
<p>In ordinary programming you write the rules. A shop owner might code: "if the customer has bought more than five times, give a discount". In <strong>machine learning</strong> you do the opposite. You give the computer many <em>examples</em> together with the answers, and it works out the rules itself. The result of that learning is called a <strong>model</strong>: a mathematical recipe that turns new inputs into predictions.</p>
<p>Machine learning is useful when the rules are too many or too fuzzy to write by hand. Nobody can list every word pattern that makes an SMS a scam, but a model can learn it from thousands of labelled messages.</p>

<h3>Words you will meet all course</h3>
<table>
<thead><tr><th>Word</th><th>Meaning</th><th>Example</th></tr></thead>
<tbody>
<tr><td>Sample (row)</td><td>One example</td><td>One house, one customer, one email</td></tr>
<tr><td>Feature (column)</td><td>A measured input about a sample</td><td>Size of the house, age of the customer</td></tr>
<tr><td>Label (target)</td><td>The answer we want to predict</td><td>The price, or "will repay: yes or no"</td></tr>
<tr><td>Training</td><td>Letting the algorithm learn from examples</td><td>Calling <code>fit</code> in scikit-learn</td></tr>
<tr><td>Prediction</td><td>The model's answer for a new sample</td><td>Estimated price of a house it has never seen</td></tr>
</tbody>
</table>

<h3>The main problem types</h3>
<ol>
<li><strong>Supervised learning</strong> uses examples that already have labels. It has two common forms:
<ul>
<li><strong>Regression</strong> predicts a number: the price of a plot of land in UGX, tomorrow's rainfall in millimetres, the yield of a maize field.</li>
<li><strong>Classification</strong> predicts a category: spam or not spam, malaria test positive or negative, which of three crop diseases a leaf shows.</li>
</ul></li>
<li><strong>Unsupervised learning</strong> has no labels. The algorithm looks for structure by itself. <strong>Clustering</strong> groups similar samples, for example grouping mobile money customers by how they spend, so that a bank can design services for each group.</li>
<li><strong>Reinforcement learning</strong> learns by trial and error from rewards, like a program that learns to play a game. It is outside the scope of this course.</li>
</ol>

<h3>Matching a question to a type</h3>
<table>
<thead><tr><th>Question</th><th>Type</th></tr></thead>
<tbody>
<tr><td>How many boda boda rides will we get on Friday?</td><td>Regression</td></tr>
<tr><td>Will this loan be repaid?</td><td>Classification</td></tr>
<tr><td>Which students are similar in how they use our e-learning site?</td><td>Clustering</td></tr>
<tr><td>Which of these 10,000 photos show a damaged road?</td><td>Classification</td></tr>
</tbody>
</table>
<p>A useful trick: look at the answer you want. A number on a scale means regression. A named group means classification. If you do not have an answer column at all, you are probably in unsupervised territory.</p>

<h3>What machine learning is not</h3>
<p>A model is not magic and not a person. It only knows the patterns that were present in its examples. If the examples were collected only in Kampala, it may fail in Gulu. If the labels contain human mistakes, the model learns those mistakes. Keep this in mind, because the later lessons on evaluation and bias are all about discovering when a model cannot be trusted.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write down five prediction questions from your own school, shop or community.</li>
<li>For each question write the label you would predict and whether it is a number or a category.</li>
<li>Mark each one as regression, classification or clustering.</li>
<li>For your best question, list three features you could measure.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Machine learning learns rules from examples instead of using rules written by a person.</li>
<li>Features are the inputs, the label is the answer, and the trained result is a model.</li>
<li>Regression predicts numbers, classification predicts categories, clustering finds groups without labels.</li>
<li>A model only knows what its examples taught it.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Setting up scikit-learn and the machine learning workflow',
                    'minutes' => 14,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to install scikit-learn, check that it works and describe the steps of every machine learning project. A fixed workflow keeps you organised and stops you from making the classic mistakes that later lessons warn about.</p>

<h3>The workflow</h3>
<ol>
<li><strong>Define the problem.</strong> What will be predicted, for whom, and what counts as success?</li>
<li><strong>Collect and inspect the data.</strong> Look at sizes, types and missing values.</li>
<li><strong>Prepare the data.</strong> Clean it, turn text categories into numbers and select features.</li>
<li><strong>Split the data</strong> into a training part and a test part.</li>
<li><strong>Train</strong> one or more models on the training part.</li>
<li><strong>Evaluate</strong> on the test part with suitable measures.</li>
<li><strong>Improve</strong> by adjusting features or settings, always judging with data the model has not memorised.</li>
<li><strong>Deploy and monitor</strong> the model, and watch whether it keeps working.</li>
</ol>
<p>You will move back and forth between these steps. Most real projects spend more time on steps 2 and 3 than on training.</p>

<h3>Installing</h3>
<p>If you have already used a virtual environment from the data analysis course, activate it. Then install the libraries:</p>
<pre><code>python -m pip install scikit-learn pandas numpy matplotlib jupyter</code></pre>
<p>On some Windows computers the command starts with <code>py</code> instead of <code>python</code>. In Google Colab nothing needs installing, because scikit-learn is already there. The package name is <code>scikit-learn</code> but you import it as <code>sklearn</code>.</p>

<h3>Check that it works</h3>
<pre><code>import sklearn
import numpy as np
import pandas as pd

print(sklearn.__version__)
print(np.__version__)
print(pd.__version__)</code></pre>
<p>The printed numbers depend on your installation, so yours will differ from a friend's. This course works with any current scikit-learn 1.x release. If you see <code>ModuleNotFoundError</code>, the library is missing from the environment you are using.</p>

<h3>The scikit-learn pattern</h3>
<p>Almost every scikit-learn model follows the same three steps, which makes the library easy to learn. You create the model object with its settings, call <code>fit</code> with the training features and labels, then call <code>predict</code> with new features.</p>
<pre><code>from sklearn.dummy import DummyClassifier

X = [[1], [2], [3], [4]]      # features: a list of rows
y = [0, 0, 0, 1]              # labels

model = DummyClassifier(strategy="most_frequent")
model.fit(X, y)
print(model.predict([[10], [20]]))</code></pre>
<pre>[0 0]</pre>
<p>The dummy classifier ignores the features completely and always answers with the most common label in the training data, which here is 0. It is not clever, but it teaches a vital idea, the <em>baseline</em>, that you will use in lesson 4.</p>

<h3>Conventions to follow</h3>
<ul>
<li>A capital <code>X</code> holds the features (a table, 2-dimensional) and a lowercase <code>y</code> holds the labels (one column, 1-dimensional).</li>
<li>Features must be numbers. Text must be converted first.</li>
<li>Names ending with an underscore, such as <code>coef_</code>, are things the model learned during <code>fit</code>.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Install or open scikit-learn and print its version.</li>
<li>Run the dummy classifier example above.</li>
<li>Change the labels to <code>[0, 1, 1, 1]</code> and predict again. What does it answer now, and why?</li>
<li>Write the eight workflow steps from memory.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Every project follows: problem, data, preparation, split, train, evaluate, improve, deploy.</li>
<li>scikit-learn models share the same pattern: create, <code>fit</code>, <code>predict</code>.</li>
<li>X is the table of features and y is the label column.</li>
<li>A dummy model gives a baseline to beat.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Features, labels and preparing data',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to turn a messy table into numbers that a model can learn from. Models cannot read the word "Gulu" or cope with an empty cell, so preparation decides much of the quality of a project. The saying "garbage in, garbage out" is true here.</p>

<h3>Separate features from the label</h3>
<p>Suppose a small table records customers of a shop. You want to predict whether a customer returns. The column <code>returned</code> is the label, and everything else is a feature (after removing columns such as a name or a customer number, which only identify a person and carry no pattern).</p>
<pre><code>import pandas as pd

df = pd.DataFrame({
    "age":      [20, None, 30],
    "town":     ["Gulu", "Mbale", "Gulu"],
    "returned": [1, 0, 1],
})

X = df.drop(columns=["returned"])
y = df["returned"]
print(X.shape)
print(y.shape)</code></pre>
<pre>(3, 2)
(3,)</pre>

<h3>Missing values</h3>
<p>Most models stop with an error when a cell is empty. Three common choices are: drop the row (only if few rows are affected), fill with a typical value such as the <strong>median</strong> (the middle value, which is not pulled around by extreme numbers), or fill with a special value and add a column that records "was missing". Here the missing age is filled with the median of 20 and 30, which is 25.</p>
<pre><code>X = X.copy()
X["age"] = X["age"].fillna(X["age"].median())
print(X)</code></pre>
<pre>    age   town
0  20.0   Gulu
1  25.0  Mbale
2  30.0   Gulu</pre>

<h3>Categories to numbers: one-hot encoding</h3>
<p>The column <code>town</code> holds text. Replacing towns by 1, 2, 3 would be wrong because it tells the model that Mbale is "bigger" than Gulu. Instead we use <strong>one-hot encoding</strong>: one new column per category, holding True or False (or 1 or 0).</p>
<pre><code>X = pd.get_dummies(X, columns=["town"])
print(X)</code></pre>
<pre>    age  town_Gulu  town_Mbale
0  20.0       True       False
1  25.0      False        True
2  30.0       True       False</pre>
<p>In older pandas versions the new columns show 1 and 0 instead of True and False. Both work. A category with a natural order, such as "primary, secondary, tertiary", may instead be coded 1, 2, 3 because the order is real.</p>

<h3>Scaling numbers</h3>
<p>Some algorithms measure distances between samples. If one feature is income in UGX (millions) and another is age (tens), income swamps age. <strong>Standardisation</strong> rewrites each feature so that its mean is 0 and its spread is about 1. You will use it with k-nearest neighbours in lesson 7. Trees do not need scaling.</p>

<h3>Choosing features</h3>
<ul>
<li>Keep features that could plausibly relate to the label and that you will know at prediction time.</li>
<li>Remove identifiers and duplicates.</li>
<li><strong>Leakage warning:</strong> never include a feature that contains the answer or is only known after the event. Predicting "loan repaid" using a column "number of reminder calls made" leaks information from the future. A leaky model looks brilliant in testing and useless in real life.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Create the small DataFrame above in a notebook.</li>
<li>Split it into X and y and fill the missing age with the median.</li>
<li>One-hot encode the town and confirm you have three columns.</li>
<li>Add a fourth row for a customer from "Jinja" and run the steps again. How many town columns do you get?</li>
</ol>

<h3>Key points</h3>
<ul>
<li>X holds the features and y holds the label; remove identifiers.</li>
<li>Fill or drop missing values before training; the median is a safe filling.</li>
<li>Use one-hot encoding for categories with no natural order.</li>
<li>Scale numbers for distance-based models, and watch out for leakage.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Train and test sets, and beating a baseline',
                    'minutes' => 15,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to split data into training and test sets and judge a model against a simple baseline. A student who memorises last year's exam paper scores perfectly on it but may fail a new paper. A model behaves the same way, so we must test it on examples it has never seen.</p>

<h3>Why we hold data back</h3>
<p>If you train and test on the same rows, the score only shows how well the model remembered them. To estimate real performance, set aside part of the data, the <strong>test set</strong>, before training. The model learns from the <strong>training set</strong> only, and you open the test set once at the end, like a final exam.</p>

<h3>train_test_split</h3>
<pre><code>from sklearn.model_selection import train_test_split

X = [[i, i * 2] for i in range(10)]    # 10 rows, 2 features
y = [0, 0, 0, 0, 0, 0, 0, 1, 1, 1]     # 7 zeros, 3 ones

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42
)
print(len(X_train), len(X_test))</code></pre>
<pre>8 2</pre>
<p>With <code>test_size=0.2</code>, 20 percent of 10 rows (2 rows) go to the test set. The data is shuffled first, so the rows chosen are random. <code>random_state=42</code> fixes the shuffle so that you and a classmate get the same split; any whole number works and 42 is only a habit. Without it, each run gives a different split.</p>

<h3>Keeping class proportions: stratify</h3>
<p>When one class is rare, a random split can leave the test set with almost none of it. Adding <code>stratify=y</code> keeps the same proportion of each class in both parts.</p>
<pre><code>X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.3, random_state=42, stratify=y
)
print(sum(y_test), len(y_test))</code></pre>
<pre>1 3</pre>
<p>The full data has 3 ones out of 10 (30 percent), so a test set of 3 rows must contain exactly one of them. Real projects use hundreds or thousands of rows; we use ten so that you can check the arithmetic.</p>

<h3>The baseline</h3>
<p>Before you train anything clever, work out how well a trivial method does. For classification, the simplest is to always predict the most common class. In our data 7 of 10 labels are 0:</p>
<pre><code>import numpy as np

y_array = np.array(y)
print(np.mean(y_array == 0))</code></pre>
<pre>0.7</pre>
<p>A model that always answers 0 is right 70 percent of the time, so a model that reports "75 percent accuracy" has barely improved on doing nothing. For regression, the baseline is predicting the average of the training labels every time. Always ask: <strong>better than what?</strong></p>

<h3>Rules for the test set</h3>
<ul>
<li>Split before you fill missing values, scale or select features, and learn those steps from the training part only. Otherwise information leaks from the test set (you will use pipelines for this in lesson 14).</li>
<li>Do not keep trying models until one scores well on the test set. Each look at the test set uses up a little of its honesty. Use cross-validation (lesson 13) for comparing models.</li>
<li>A common split is 70 to 80 percent for training and the rest for testing.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Run the first split above and print the lengths.</li>
<li>Change <code>test_size</code> to 0.3 and predict by hand how many rows are in the test set (3).</li>
<li>Remove <code>random_state</code> and run the cell three times. Do the rows in <code>y_test</code> change?</li>
<li>Compute the baseline accuracy for a label list of your own choice.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Train on one part of the data and test on another part that the model has not seen.</li>
<li><code>random_state</code> makes a split repeatable and <code>stratify</code> keeps class proportions.</li>
<li>Compare every model with a baseline such as always predicting the majority class.</li>
<li>Split first, then prepare, so that no information leaks from the test set.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A bank wants to predict the amount of money a customer will borrow next year. Which problem type is this?
- Classification
* Regression
- Clustering
- Reinforcement learning
= The answer is a number on a scale (an amount of money), so this is regression.

Q: You have thousands of customer records but no answer column, and you want to find groups of similar customers. Which type of learning is this?
- Supervised classification
- Supervised regression
* Unsupervised learning (clustering)
- Hand-written rules
= Without labels the algorithm must find structure itself, which is unsupervised learning, and grouping is called clustering.

Q: In a table of houses with columns size, number of rooms and price, where price is to be predicted, what is the label?
* Price
- Size
- Number of rooms
- The whole table
= The label is the thing you want to predict. Size and rooms are features.

Q: Which three steps does almost every scikit-learn model follow, in order?
- predict, fit, create
* create the model, fit, predict
- fit, create, split
- scale, predict, delete
= You create the model with its settings, train it with fit, then use predict on new data.

Q: Why is replacing the towns Gulu, Mbale and Jinja by the numbers 1, 2 and 3 usually a bad idea?
- Python cannot store those numbers
- The numbers are too small
* It tells the model there is an order and distances between towns that do not really exist
- It removes the label
= One-hot encoding avoids inventing a fake order between categories.

Q: A feature records "number of reminder calls made after the loan was due" and you use it to predict whether the loan will be repaid. What is the main problem?
- The feature has too few rows
* It leaks information that is only known after the outcome, so the model will fail in real use
- It is a text feature
- Reminder calls cannot be counted
= This is leakage: the feature would not be available when the prediction must be made.

Q: Why do we keep a test set that the model never sees during training?
- To make training faster
- To give the model more rows
* To estimate how the model performs on new data rather than on data it memorised
- Because scikit-learn requires it for fit to run
= A score on the training data only shows memory. A test set estimates real performance.

Q: In a dataset 80 percent of the labels are the same class. A new model has 82 percent accuracy. What is the best conclusion?
- The model is excellent
- The model is perfect on the test set
* The baseline of always guessing the majority class already scores 80 percent, so the model gives little improvement
- Accuracy cannot be computed on such data
= Always compare with a baseline. Here a trivial rule nearly matches the model.
QUIZ,
        ],
        [
            'title' => 'Module 2: Predicting with simple models',
            'summary' => 'Fit straight-line and logistic models, understand how they work and use k-nearest neighbours with properly scaled data.',
            'lessons' => [
                [
                    'title' => 'Linear regression',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to fit a straight-line model, read its coefficient and intercept and measure its error. Linear regression is the oldest and most explainable prediction model, and the first one most analysts try.</p>

<h3>The idea</h3>
<p>With one feature, linear regression draws the straight line that best fits the points. The line is described by <code>y = b0 + b1 * x</code>. Here <code>b1</code> is the <strong>coefficient</strong> (the slope: how much y changes when x increases by 1) and <code>b0</code> is the <strong>intercept</strong> (the value of y when x is 0). With many features there is one coefficient per feature.</p>
<p>"Best fit" means the line that makes the total of the squared gaps between the points and the line as small as possible. This is called <strong>least squares</strong>. Squaring makes every gap positive and punishes big gaps more than small ones.</p>

<h3>A perfect line</h3>
<p>Take four points where y is always twice x plus one.</p>
<pre><code>from sklearn.linear_model import LinearRegression

X = [[1], [2], [3], [4]]
y = [3, 5, 7, 9]

model = LinearRegression()
model.fit(X, y)

print(round(model.coef_[0], 2))
print(round(model.intercept_, 2))
print(round(float(model.predict([[5]])[0]), 2))</code></pre>
<pre>2.0
1.0
11.0</pre>
<p>The model recovered the rule exactly: slope 2, intercept 1, so for x = 5 it predicts 2 times 5 plus 1, which is 11. We rounded because floating-point numbers can print tiny trailing digits.</p>

<h3>Points that do not lie on a line</h3>
<p>Real data is noisy. Use three points: x = 1, 2, 3 and y = 2, 4, 5. By hand: the mean of x is 2 and the mean of y is 11/3. The slope is the sum of (x minus mean x)(y minus mean y) divided by the sum of (x minus mean x) squared. That is 3 divided by 2, so 1.5. The intercept is 11/3 minus 1.5 times 2, which is about 0.67.</p>
<pre><code>X = [[1], [2], [3]]
y = [2, 4, 5]
model = LinearRegression().fit(X, y)
print(round(model.coef_[0], 2), round(model.intercept_, 2))

pred = model.predict(X)
print(pred.round(2))</code></pre>
<pre>1.5 0.67
[2.17 3.67 5.17]</pre>
<p>The line misses each point by a small amount: -0.17, 0.33 and -0.17. These gaps are the <strong>residuals</strong> (actual minus predicted). The mean of their squares is the <strong>mean squared error (MSE)</strong>, which here is about 0.0556. You will meet error measures again in lesson 12.</p>

<h3>Many features</h3>
<p>The code is identical when X has several columns. For house prices you might use size, rooms and distance to town; the model learns one coefficient for each. A positive coefficient means the price goes up as that feature goes up while the others stay fixed. Coefficients are only comparable when the features are on similar scales.</p>

<h3>Strengths and limits</h3>
<ul>
<li>Fast, simple and easy to explain to a client.</li>
<li>It can only draw straight lines (or flat planes). If the real pattern curves, the model underfits unless you add features such as the square of x.</li>
<li>Outliers, extreme values far from the others, pull the line strongly towards themselves.</li>
<li>Prediction far outside the range of the training data (extrapolation) is risky. A line fitted on houses of 50 to 200 square metres says little about a 2000 square metre estate.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Fit the perfect-line example and confirm the slope and intercept.</li>
<li>Change the last y value from 9 to 12. Predict whether the slope will grow or shrink, then run it.</li>
<li>Invent six points about hours studied and exam marks, fit a line and predict the mark for 5 hours.</li>
<li>Explain in one sentence what your coefficient means in plain English.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Linear regression fits y = intercept + coefficient times x by least squares.</li>
<li>The coefficient is the change in y for a one-unit change in x.</li>
<li>Residuals are the gaps between actual and predicted values.</li>
<li>It cannot capture curves on its own, and outliers and extrapolation can mislead it.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Logistic regression for classification',
                    'minutes' => 17,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to train a logistic regression classifier and explain what its probabilities mean. Despite its name it solves classification problems such as "will this student pass?" or "is this message spam?", and it is a standard first model for yes-or-no questions.</p>

<h3>From a line to a probability</h3>
<p>For a yes-or-no label coded as 0 and 1, a straight line is a poor choice because it can output values below 0 or above 1. Logistic regression takes a linear score and passes it through the <strong>sigmoid function</strong>, which squeezes any number into the range 0 to 1:</p>
<pre><code>import numpy as np

def sigmoid(z):
    return 1 / (1 + np.exp(-z))

print(sigmoid(0))
print(round(sigmoid(2), 4))
print(round(sigmoid(-2), 4))</code></pre>
<pre>0.5
0.8808
0.1192</pre>
<p>A score of 0 means "50-50". A large positive score means "very likely class 1" and a large negative score means "very likely class 0". The output can be read as the model's estimated probability of class 1. By default scikit-learn predicts class 1 when that probability is above 0.5.</p>

<h3>Training a classifier</h3>
<p>Let X be hours studied and y be 1 for pass and 0 for fail.</p>
<pre><code>from sklearn.linear_model import LogisticRegression

X = [[1], [2], [3], [4], [5], [6]]
y = [0, 0, 0, 1, 1, 1]

clf = LogisticRegression()
clf.fit(X, y)

print(clf.predict([[1], [6]]))
proba = clf.predict_proba([[1]])
print(proba.shape, round(proba.sum(), 2))</code></pre>
<pre>[0 1]
(1, 2) 1.0</pre>
<p>A student who studied 1 hour is predicted to fail, and one who studied 6 hours to pass. <code>predict_proba</code> returns one row per sample and one column per class, here the probability of 0 and of 1, and each row adds up to 1. You can read exactly which probability belongs to which class from <code>clf.classes_</code>.</p>

<h3>Changing the threshold</h3>
<p>The 0.5 cut-off is a choice, not a law. A clinic that screens for a serious illness may prefer to flag anyone with probability above 0.2, accepting more false alarms in order to miss fewer sick patients. You apply your own threshold like this:</p>
<pre><code>p1 = clf.predict_proba(X)[:, 1]     # probability of class 1
custom = (p1 &gt; 0.2).astype(int)</code></pre>
<p>Lesson 11 explains how to choose a threshold by balancing precision and recall.</p>

<h3>Regularisation: the setting C</h3>
<p>LogisticRegression applies <strong>regularisation</strong> by default, a gentle penalty on very large coefficients that stops the model from becoming over-confident. The parameter <code>C</code> controls it, and strangely it works in reverse: a <em>small</em> C means <em>strong</em> regularisation. The default of 1.0 is a sensible start.</p>

<h3>More than two classes</h3>
<p>If the label has three or more categories, for example three crop diseases, the same code works. scikit-learn handles the extra classes automatically and <code>predict_proba</code> gives one column per class.</p>

<h3>Strengths and limits</h3>
<ul>
<li>Quick, stable, and its coefficients show which features push towards class 1.</li>
<li>The decision boundary is a straight line (or plane), so it cannot separate classes arranged in a ring.</li>
<li>Features on very different scales train more reliably after standardisation.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Train the pass-or-fail classifier above and predict for 3.5 hours.</li>
<li>Print <code>clf.classes_</code> and the probabilities for 3.5 hours. Which column is the pass probability?</li>
<li>Compute sigmoid of 1 and of -1 and notice that they add up to 1.</li>
<li>Apply a threshold of 0.3 to your probabilities and see which predictions change.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Logistic regression is a classifier: it turns a linear score into a probability with the sigmoid.</li>
<li><code>predict</code> gives the class; <code>predict_proba</code> gives the probabilities, each row adding to 1.</li>
<li>The threshold can be moved to fit the cost of different mistakes.</li>
<li>A smaller C means stronger regularisation.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'k-nearest neighbours and feature scaling',
                    'minutes' => 17,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to explain and use k-nearest neighbours (k-NN), and you will understand why scaling the features matters. The method is easy to picture: to predict for a new sample, look at the most similar samples you already know and let them vote.</p>

<h3>How k-NN works</h3>
<ol>
<li>Measure the distance from the new sample to every training sample (usually the straight-line, or <strong>Euclidean</strong>, distance).</li>
<li>Pick the <code>k</code> closest samples.</li>
<li>For classification, take a majority vote of their labels. For regression, take the average of their values.</li>
</ol>
<p>k-NN does almost no work in <code>fit</code>: it just stores the data. All the effort happens at prediction time, so it becomes slow when there are very many samples.</p>

<h3>A worked example</h3>
<p>Four known points: (1, 1) and (2, 1) are class 0; (8, 8) and (9, 8) are class 1. A new point (2, 2) arrives, and we use k = 3. The distances are: to (2, 1) exactly 1; to (1, 1) about 1.41; to (8, 8) about 8.49; to (9, 8) about 9.22. The three nearest are (2, 1) and (1, 1) with class 0 and (8, 8) with class 1, so the vote is two against one for class 0.</p>
<pre><code>from sklearn.neighbors import KNeighborsClassifier

X = [[1, 1], [2, 1], [8, 8], [9, 8]]
y = [0, 0, 1, 1]

knn = KNeighborsClassifier(n_neighbors=3)
knn.fit(X, y)
print(knn.predict([[2, 2]]))
print(knn.predict([[9, 9]]))</code></pre>
<pre>[0]
[1]</pre>
<p>The point (9, 9) is closest to (9, 8) and (8, 8), both class 1, so class 1 wins.</p>

<h3>Choosing k</h3>
<p>A very small k, such as 1, follows every little wobble in the data and is easily fooled by one mislabelled sample: it overfits. A very large k smooths too much and ignores local detail: it underfits. With two classes an odd k avoids tied votes. Try several values and compare them with cross-validation (lesson 13).</p>

<h3>Why scaling is essential</h3>
<p>Imagine predicting loan risk from age (20 to 60) and monthly income in UGX (200,000 to 5,000,000). The income differences are tens of thousands of times larger than the age differences, so the distance is decided almost only by income and age is ignored. <strong>Standardisation</strong> subtracts each feature's mean and divides by its standard deviation, so all features have mean 0 and similar spread.</p>
<pre><code>import numpy as np
from sklearn.preprocessing import StandardScaler

values = np.array([[1], [2], [3]])
scaler = StandardScaler()
scaled = scaler.fit_transform(values)
print(scaled.ravel().round(4))</code></pre>
<pre>[-1.2247  0.      1.2247]</pre>
<p>The mean is 2 and the standard deviation is about 0.8165, so 1 becomes -1.2247, 2 becomes 0 and 3 becomes 1.2247. The rule: <strong>fit the scaler on the training data only</strong>, then use <code>transform</code> on the test data so that the test set's numbers are not used to learn anything.</p>

<h3>Strengths and limits</h3>
<ul>
<li>Simple, no training step, and it works with curved boundaries.</li>
<li>Needs scaled features, is slow on large data and struggles when there are very many features, because distances become less meaningful.</li>
<li>It does not explain itself well beyond "these were the closest examples".</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Run the k-NN example and then change <code>n_neighbors</code> to 1. Does the answer for (2, 2) change?</li>
<li>Add a mislabelled point (2, 2) with label 1 to the data and test k = 1 and k = 3.</li>
<li>Scale the numbers 10, 20, 30 with StandardScaler and check that the middle one becomes 0.</li>
<li>Explain in your own words why income would dominate age without scaling.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>k-NN predicts by a vote (or average) of the k closest training samples.</li>
<li>Small k overfits; large k underfits.</li>
<li>Distance-based models need standardised features; fit the scaler on training data only.</li>
<li>All the work happens at prediction time, so it gets slow on big datasets.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In the line y = 1 + 2x fitted by linear regression, what does the coefficient 2 mean?
- y is always 2
- When x is 0, y is 2
* When x increases by 1, the predicted y increases by 2
- The error is 2
= The coefficient is the slope: the change in y for each one-unit change in x. The intercept is 1.

Q: What do linear regression models minimise when they fit by least squares?
- The number of features
* The total of the squared gaps between the points and the line
- The number of outliers
- The size of the test set
= Least squares chooses the line with the smallest sum of squared residuals.

Q: A line fitted on houses of 50 to 200 square metres is used to price a 2000 square metre estate. What is the risk?
- None, linear models never fail
* Extrapolation far outside the training range is unreliable
- The model will refuse to run
- The coefficient becomes negative
= Predictions outside the range of the data the model saw can be badly wrong.

Q: What does the sigmoid function do in logistic regression?
- It splits the data into train and test
* It squeezes any number into the range 0 to 1 so it can be read as a probability
- It scales the features
- It chooses the best k
= The linear score is passed through the sigmoid to produce a probability of class 1.

Q: A clinic wants to catch as many sick patients as possible, even at the cost of more false alarms. What should it consider?
- Raising the probability threshold above 0.5
* Lowering the probability threshold below 0.5
- Removing the test set
- Using a larger C
= A lower threshold flags more people as positive, which finds more sick patients but raises false alarms.

Q: In scikit-learn's LogisticRegression, what does a smaller value of C mean?
- Weaker regularisation
- Fewer rows are used
* Stronger regularisation
- A different classification type
= C is the inverse of regularisation strength, so smaller C means a stronger penalty.

Q: With k = 3, a new point has neighbours labelled 1, 0 and 0. What does k-NN classification predict?
- 1
* 0
- 0.33
- It cannot decide
= The majority of the three nearest neighbours is class 0.

Q: Why should you standardise features before using k-nearest neighbours?
- Because k-NN cannot read numbers
- To increase the number of rows
* Otherwise features with large numeric ranges, such as income, dominate the distance
- To remove the label
= Distances are driven by the biggest numbers, so unscaled features can be ignored unfairly.
QUIZ,
        ],
        [
            'title' => 'Module 3: Trees, forests and clusters',
            'summary' => 'Learn decision trees, combine many trees into a random forest and group unlabelled data with k-means clustering.',
            'lessons' => [
                [
                    'title' => 'Decision trees',
                    'minutes' => 17,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to explain how a decision tree makes a prediction, train one, read its rules and control its size. Trees are popular because they work like a flow chart that a non-technical person can follow, and they need little data preparation.</p>

<h3>A tree is a set of questions</h3>
<p>A decision tree asks a series of yes-or-no questions about the features, such as "Is the number of hours studied at most 2.5?" Each answer sends the sample down one branch until it reaches a <strong>leaf</strong>, which holds the prediction. For classification the leaf gives the most common class among the training samples that arrived there; for regression it gives their average value.</p>

<h3>How the best question is chosen</h3>
<p>At each step the algorithm tries many possible questions and keeps the one that makes the groups most <strong>pure</strong>, meaning each group is mostly one class. A common purity measure is the <strong>Gini impurity</strong>: one minus the sum of the squared class proportions. A group that is all one class has impurity 0. For a group with 6 samples of one class and 4 of the other:</p>
<pre><code>p_yes = 6 / 10
p_no  = 4 / 10
gini = 1 - p_yes**2 - p_no**2
print(round(gini, 2))</code></pre>
<pre>0.48</pre>
<p>A perfectly mixed two-class group (5 and 5) has impurity 0.5, which is the worst case for two classes. The tree picks the split that lowers the weighted average impurity of the two resulting groups the most, and then repeats the process inside each group.</p>

<h3>Training and reading a tree</h3>
<pre><code>from sklearn.tree import DecisionTreeClassifier, export_text

X = [[1], [2], [3], [4]]          # hours studied
y = [0, 0, 1, 1]                  # 0 = fail, 1 = pass

tree = DecisionTreeClassifier(max_depth=1, random_state=0)
tree.fit(X, y)

print(tree.predict([[1.5], [3.5]]))
print(export_text(tree, feature_names=["hours"]))</code></pre>
<pre>[0 1]
|--- hours &lt;= 2.50
|   |--- class: 0
|--- hours &gt;  2.50
|   |--- class: 1</pre>
<p>The tree placed its threshold halfway between 2 and 3, at 2.5. Anyone can read this as a rule: studied up to 2.5 hours, predicted fail; more than that, predicted pass. For a real project <code>export_text</code> lets you check that the rules make sense to a domain expert.</p>

<h3>The danger: growing too deep</h3>
<p>If you let a tree grow without limit it keeps splitting until every training sample sits alone in a pure leaf. It then scores perfectly on the training data by memorising it, including every accident and error. This is <strong>overfitting</strong>, and the tree performs worse on new data. To control it, limit the tree:</p>
<table>
<thead><tr><th>Setting</th><th>Effect</th></tr></thead>
<tbody>
<tr><td><code>max_depth</code></td><td>Maximum number of questions in a row</td></tr>
<tr><td><code>min_samples_leaf</code></td><td>Every leaf must keep at least this many samples</td></tr>
<tr><td><code>min_samples_split</code></td><td>A group needs at least this many samples before it may be split</td></tr>
</tbody>
</table>
<p>A shallow tree may underfit and a deep tree overfits, so the best depth is found by testing, as you will do in lesson 13.</p>

<h3>Strengths and limits</h3>
<ul>
<li>Easy to explain, handles numbers and one-hot categories, and needs no scaling.</li>
<li>Unstable: a small change in the data can change the tree a lot.</li>
<li>Splits are straight cuts along one feature, so diagonal patterns need many cuts.</li>
<li>A regression tree predicts steps, not smooth curves, and cannot predict outside the range of its training labels.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Run the tree example and print the rules.</li>
<li>Change the labels to <code>[0, 1, 1, 1]</code>. Where do you expect the new threshold? (Between 1 and 2, so 1.5.)</li>
<li>Compute the Gini impurity of a group with 3 of one class and 1 of the other (0.375).</li>
<li>Explain why a tree with no depth limit usually scores 100 percent on its own training data.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A tree asks yes-or-no questions and ends in leaves that hold predictions.</li>
<li>Splits are chosen to make groups purer, for example by lowering Gini impurity.</li>
<li>Unlimited trees memorise the training data, so limit depth or leaf size.</li>
<li>Trees are easy to read but unstable.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Random forests and feature importance',
                    'minutes' => 17,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to train a random forest, explain why it is usually more reliable than a single tree and read which features it relied on. Random forests are among the most useful all-round models for tables of data, and they are strong even with default settings.</p>

<h3>The wisdom of many trees</h3>
<p>One tree is unstable, but an average of many different trees is much steadier. This is the idea of <strong>ensemble learning</strong>: combine many weak or noisy models to get one strong one. Think of asking a hundred villagers to estimate the weight of a goat: the average of their guesses is usually closer than most individual guesses.</p>
<p>A random forest makes its trees different in two ways:</p>
<ol>
<li><strong>Bootstrap sampling (bagging).</strong> Each tree is trained on a random sample of the rows, drawn with replacement, so some rows appear twice and others not at all.</li>
<li><strong>Random feature choice.</strong> At every split, a tree considers only a random subset of the features.</li>
</ol>
<p>For classification the forest takes a vote of all trees; for regression it averages their predictions. Because the trees make different mistakes, the mistakes tend to cancel out, which reduces overfitting compared with a single deep tree.</p>

<h3>Training a forest</h3>
<pre><code>from sklearn.ensemble import RandomForestClassifier

X = [[1, 0], [2, 1], [3, 0], [8, 1], [9, 0], [10, 1]]
y = [0, 0, 0, 1, 1, 1]

forest = RandomForestClassifier(n_estimators=100, random_state=0)
forest.fit(X, y)

print(forest.predict([[2, 0], [9, 1]]))
print(len(forest.estimators_))</code></pre>
<pre>[0 1]
100</pre>
<p>The first feature separates the classes cleanly, so the forest answers 0 for a small value and 1 for a large one. <code>n_estimators</code> is the number of trees; more trees give steadier results but take longer, and 100 is a good start. Fixing <code>random_state</code> makes the result repeatable, because the forest uses randomness.</p>

<h3>Which features mattered?</h3>
<p>A fitted forest reports <code>feature_importances_</code>: one number per feature, showing how much that feature helped to reduce impurity across all trees. The numbers add up to 1.</p>
<pre><code>print(round(forest.feature_importances_.sum(), 2))
print(forest.feature_importances_[0] &gt; forest.feature_importances_[1])</code></pre>
<pre>1.0
True</pre>
<p>The second line is True because only the first feature separates the classes, so it must carry the larger share. In a real project you would pair the numbers with the column names in a pandas Series and sort them to see the ranking.</p>
<p>Be careful with these importances. They show what the model <em>used</em>, not what <em>causes</em> the outcome. If two features carry similar information, the importance is split between them. Features with many distinct values can look more important than they are.</p>

<h3>Useful settings</h3>
<table>
<thead><tr><th>Setting</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>n_estimators</code></td><td>How many trees</td></tr>
<tr><td><code>max_depth</code></td><td>Limit on the depth of each tree</td></tr>
<tr><td><code>min_samples_leaf</code></td><td>Smallest allowed leaf; larger values smooth the model</td></tr>
<tr><td><code>n_jobs=-1</code></td><td>Use all processor cores to train faster</td></tr>
</tbody>
</table>

<h3>Strengths and limits</h3>
<ul>
<li>Accurate with little tuning, works with mixed features and needs no scaling.</li>
<li>Gives importances for a first understanding of the data.</li>
<li>Harder to explain than one tree, because a hundred trees cannot be drawn on one page.</li>
<li>Slower and bigger than a single tree, and, like all tree models, it cannot predict values outside the range of the training labels.</li>
</ul>
<p>The sibling method, <strong>gradient boosting</strong>, builds trees one after another, each fixing the mistakes of the last. It is often even more accurate, and you can explore it later in the scikit-learn guide.</p>

<h3>Try it yourself</h3>
<ol>
<li>Run the forest example and print the feature importances.</li>
<li>Swap the two feature columns and check that the importance moves with the column.</li>
<li>Train a forest with <code>n_estimators=10</code> and again with 300 and check that the predictions agree.</li>
<li>In your own words, explain why averaging many different trees reduces overfitting.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A random forest averages many trees trained on random samples of rows and features.</li>
<li>It overfits less than a single deep tree and works well with defaults.</li>
<li><code>feature_importances_</code> adds up to 1 and shows what the model used, not what causes the outcome.</li>
<li>It is less explainable than a single tree.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Clustering with k-means',
                    'minutes' => 16,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to group unlabelled samples with k-means and judge how many groups make sense. Clustering is useful when nobody has labelled the data, for instance when a telecom company wants to discover types of customers or a school wants to group learners by how they study.</p>

<h3>The k-means algorithm</h3>
<p>You choose the number of clusters, <code>k</code>. The algorithm then repeats two simple steps:</p>
<ol>
<li>Place k centres (centroids) at random starting points among the data.</li>
<li><strong>Assign</strong> every sample to its nearest centroid.</li>
<li><strong>Update</strong> each centroid to the mean position of the samples assigned to it.</li>
<li>Repeat steps 2 and 3 until the assignments stop changing.</li>
</ol>
<p>The method finds roughly round groups of similar size. It always returns k clusters, whether or not real groups exist, so the answer must be checked with common sense.</p>

<h3>A small example</h3>
<p>Six points form two obvious groups: three near (1, 1) and three near (8, 8).</p>
<pre><code>import numpy as np
from sklearn.cluster import KMeans

X = np.array([[1, 1], [1.5, 2], [1, 0],
              [8, 8], [9, 9], [8, 9]])

km = KMeans(n_clusters=2, n_init=10, random_state=0)
km.fit(X)

print(sorted(np.bincount(km.labels_)))
centres = km.cluster_centers_
print(centres[np.argsort(centres[:, 0])].round(2))</code></pre>
<pre>[3, 3]
[[1.17 1.  ]
 [8.33 8.67]]</pre>
<p>The two clusters each hold 3 points. We sort the centres because the cluster numbers 0 and 1 are arbitrary and may swap between runs. You can verify the centres by hand: the first group has mean x = (1 + 1.5 + 1) / 3 = 1.17 and mean y = (1 + 2 + 0) / 3 = 1, and the second group has mean x = 8.33 and mean y = 8.67.</p>
<p>The setting <code>n_init=10</code> runs the algorithm from ten different starting points and keeps the best, because a bad start can lead to a poor result.</p>

<h3>Always scale first</h3>
<p>Like k-NN, k-means uses distances, so standardise the features before clustering. Otherwise the feature with the biggest numbers decides the groups.</p>

<h3>Choosing k</h3>
<p>The model reports <code>inertia_</code>: the total squared distance from each point to its centre. Inertia always falls when k grows, reaching zero when each point is its own cluster, so you cannot simply pick the lowest. Instead run k = 1, 2, 3, and so on, and plot inertia against k. Look for the <strong>elbow</strong>, the point where the curve stops dropping steeply. Another help is the <strong>silhouette score</strong> (<code>sklearn.metrics.silhouette_score</code>), which runs from -1 to 1; higher means clusters are tight and well separated. For our six points the silhouette score for k = 2 is high, which agrees with what we see.</p>

<h3>Using the clusters</h3>
<p>Clusters are only numbers until a person interprets them. Compute the average of each feature per cluster, for example with <code>df.groupby(labels).mean()</code>, and give each group a meaning: "high spenders who visit rarely", "small frequent buyers". Test the story with colleagues who know the customers. Because there are no labels, there is no accuracy score, and the quality of a clustering is judged by whether it is stable and useful.</p>

<h3>Limits</h3>
<ul>
<li>You must choose k.</li>
<li>It struggles with groups of very different sizes or odd shapes, such as a ring around a blob.</li>
<li>It is sensitive to outliers, which drag centres away.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Run the six-point example and confirm that the clusters hold 3 points each.</li>
<li>Add a seventh point (5, 5) and run again with k = 2. Which group does it join?</li>
<li>Run k = 3 on the original six points and print <code>km.inertia_</code> for k = 1, 2 and 3. Does it fall each time?</li>
<li>Write a one-line meaning for each of your two clusters.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>k-means alternates between assigning points to the nearest centre and moving centres to the group means.</li>
<li>Scale features, use several starts and choose k with the elbow or silhouette score.</li>
<li>Cluster numbers are arbitrary labels; their meaning comes from studying each group.</li>
<li>It always returns k groups, so check that they are real and useful.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: What does a leaf of a classification decision tree contain?
- The feature with the highest importance
* The predicted class, usually the most common class among the training samples that reached it
- A random number
- The whole training set
= A leaf is the end of the question path and holds the prediction.

Q: A group of samples has 10 of class A and 0 of class B. What is its Gini impurity?
* 0
- 0.5
- 1
- 10
= One minus 1 squared minus 0 squared is 0. The group is perfectly pure.

Q: A decision tree with no depth limit scores 100 percent on the training data but much lower on the test data. What is this called?
- Underfitting
- Standardisation
* Overfitting
- Stratification
= The tree memorised the training data, including noise, and fails to generalise.

Q: Which settings help to stop a decision tree from overfitting? (choose all that apply)
* max_depth
* min_samples_leaf
- n_neighbors
- Setting random_state to 42
= Limiting depth and requiring larger leaves both make the tree simpler. n_neighbors belongs to k-NN and random_state only fixes randomness.

Q: Why does a random forest usually overfit less than one deep tree?
- It uses only one feature
* It averages many different trees trained on random samples of rows and features, so their mistakes tend to cancel
- It never uses the training data
- It always has depth 1
= Diversity among the trees plus averaging reduces variance.

Q: A random forest reports that one feature has the highest importance. What can you safely conclude?
- That feature causes the outcome
- All other features can be deleted from the real world
* The model relied heavily on that feature, but this does not prove that it causes the outcome
- The model has 100 percent accuracy
= Importance reflects use by the model, not cause and effect.

Q: What does the k in k-means stand for?
- The number of features
* The number of clusters you ask for
- The number of neighbours that vote
- The number of training rows
= In k-means you choose how many clusters to look for. The number of neighbours is the k of k-NN.

Q: You run k-means for k = 1 to 8 and plot the inertia. How do you use the plot?
- Choose the k with the lowest inertia
* Look for the elbow where the curve stops dropping steeply
- Choose the highest k
- Ignore it, because inertia is random
= Inertia always falls as k grows, so you look for the point of diminishing returns, then check that the clusters make sense.
QUIZ,
        ],
        [
            'title' => 'Module 4: Evaluating models properly',
            'summary' => 'Measure classification and regression models with the right numbers, recognise overfitting and use cross-validation and tuning without cheating.',
            'lessons' => [
                [
                    'title' => 'Evaluating classifiers: accuracy, precision, recall and the confusion matrix',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to build a confusion matrix and calculate accuracy, precision, recall and the F1 score. A single accuracy number can hide serious failures, so you must know which measure fits the cost of each kind of mistake.</p>

<h3>The confusion matrix</h3>
<p>Take a test for a disease where 1 means sick (the "positive" class) and 0 means healthy. Every prediction falls into one of four boxes:</p>
<table>
<thead><tr><th></th><th>Predicted healthy (0)</th><th>Predicted sick (1)</th></tr></thead>
<tbody>
<tr><td><strong>Actually healthy (0)</strong></td><td>True negative (TN)</td><td>False positive (FP)</td></tr>
<tr><td><strong>Actually sick (1)</strong></td><td>False negative (FN)</td><td>True positive (TP)</td></tr>
</tbody>
</table>
<p>A false positive is a false alarm; a false negative is a missed case. In scikit-learn the matrix has the true classes as rows and the predicted classes as columns, in the order 0 then 1.</p>
<pre><code>from sklearn.metrics import confusion_matrix

y_true = [1, 1, 1, 1, 0, 0, 0, 0, 0, 0]
y_pred = [1, 1, 1, 0, 1, 0, 0, 0, 0, 0]

print(confusion_matrix(y_true, y_pred))</code></pre>
<pre>[[5 1]
 [1 3]]</pre>
<p>Reading it: 5 healthy people were correctly cleared (TN), 1 healthy person was wrongly flagged (FP), 1 sick person was missed (FN) and 3 sick people were found (TP).</p>

<h3>Four measures</h3>
<table>
<thead><tr><th>Measure</th><th>Formula</th><th>Question it answers</th><th>Our example</th></tr></thead>
<tbody>
<tr><td>Accuracy</td><td>(TP + TN) / all</td><td>How often is the model right overall?</td><td>8 / 10 = 0.8</td></tr>
<tr><td>Precision</td><td>TP / (TP + FP)</td><td>Of those flagged positive, how many really are?</td><td>3 / 4 = 0.75</td></tr>
<tr><td>Recall</td><td>TP / (TP + FN)</td><td>Of all real positives, how many did we find?</td><td>3 / 4 = 0.75</td></tr>
<tr><td>F1 score</td><td>2 x P x R / (P + R)</td><td>One number balancing precision and recall</td><td>0.75</td></tr>
</tbody>
</table>
<pre><code>from sklearn.metrics import (accuracy_score, precision_score,
                             recall_score, f1_score)

print(accuracy_score(y_true, y_pred))
print(precision_score(y_true, y_pred))
print(recall_score(y_true, y_pred))
print(f1_score(y_true, y_pred))</code></pre>
<pre>0.8
0.75
0.75
0.75</pre>

<h3>Why accuracy alone can fool you</h3>
<p>Suppose 10 of 1,000 patients have a serious infection. A lazy "model" that says "healthy" to everyone is right 990 times, so its accuracy is 99 percent. But its recall is 0, because it finds none of the sick patients. When one class is rare, accuracy hides the failure. Always look at precision and recall for the class you care about.</p>

<h3>Which mistake is worse?</h3>
<ul>
<li><strong>Missing a case is costly</strong> (cancer screening, fraud detection, detecting a flood): aim for high recall and accept some false alarms.</li>
<li><strong>A false alarm is costly</strong> (blocking a customer's mobile money account, marking a genuine email as spam): aim for high precision.</li>
</ul>
<p>There is usually a trade-off. Lowering the probability threshold, as in the logistic regression lesson, raises recall and lowers precision; raising it does the opposite. The <code>classification_report</code> function prints precision, recall and F1 for each class in one table, which is a convenient summary to show your team.</p>

<h3>Try it yourself</h3>
<ol>
<li>Run the confusion matrix example and identify TN, FP, FN and TP.</li>
<li>Change one more prediction so that the second sick person is also missed. Recompute recall by hand (2/4 = 0.5), then check with scikit-learn.</li>
<li>Make up a rare-class example with 5 positives out of 100 and show that "always negative" has 95 percent accuracy and 0 recall.</li>
<li>Decide whether precision or recall matters more for a spam filter and write one sentence of reasoning.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The confusion matrix counts TN, FP, FN and TP; rows are true classes and columns are predictions.</li>
<li>Precision asks "how many flagged were right", recall asks "how many real cases were found".</li>
<li>Accuracy is misleading when classes are unbalanced.</li>
<li>Choose the measure by the cost of each kind of mistake.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Evaluating regression models and spotting overfitting',
                    'minutes' => 17,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to compute MAE, MSE, RMSE and R squared for a regression model and recognise overfitting by comparing training and test scores. These tools tell you how far off your predictions are, in a language that a manager or client can understand.</p>

<h3>Error measures for numbers</h3>
<p>For each sample the <strong>error</strong> is the actual value minus the predicted value. Because positive and negative errors would cancel out, we summarise them in ways that remove the sign:</p>
<table>
<thead><tr><th>Measure</th><th>How it is calculated</th><th>Notes</th></tr></thead>
<tbody>
<tr><td>MAE (mean absolute error)</td><td>Average of the absolute errors</td><td>Same units as the label; easy to explain</td></tr>
<tr><td>MSE (mean squared error)</td><td>Average of the squared errors</td><td>Punishes large errors heavily; units are squared</td></tr>
<tr><td>RMSE (root mean squared error)</td><td>Square root of the MSE</td><td>Back in the original units</td></tr>
<tr><td>R squared</td><td>1 minus (model error / error of always predicting the mean)</td><td>1 is perfect, 0 is no better than the mean</td></tr>
</tbody>
</table>
<p>Work through a tiny example. Actual values are 3, 5 and 7, and predictions are 2, 5 and 9. The errors are +1, 0 and -2. The MAE is (1 + 0 + 2) / 3 = 1.0. The MSE is (1 + 0 + 4) / 3, about 1.6667, and the RMSE is its square root, about 1.2910.</p>
<pre><code>from sklearn.metrics import (mean_absolute_error,
                             mean_squared_error, r2_score)

y_true = [3, 5, 7]
y_pred = [2, 5, 9]

print(mean_absolute_error(y_true, y_pred))
print(round(mean_squared_error(y_true, y_pred), 4))
print(round(mean_squared_error(y_true, y_pred) ** 0.5, 4))
print(round(r2_score(y_true, y_pred), 3))</code></pre>
<pre>1.0
1.6667
1.291
0.375</pre>
<p>The R squared value uses the total variation of y around its mean (5): (4 + 0 + 4) = 8. The model's squared error total is 5, so R squared is 1 minus 5/8 = 0.375. A model that always predicts the mean would score exactly 0, and a model worse than that can score below 0.</p>
<p>When you report a result, say it in the label's units: "on average our rent predictions are about UGX 120,000 off" is far clearer than "MSE is 1.4e10".</p>

<h3>Underfitting and overfitting</h3>
<p>Every model sits somewhere between two failures:</p>
<ul>
<li><strong>Underfitting</strong>: the model is too simple (a straight line through a curve). It does badly on both the training and the test data.</li>
<li><strong>Overfitting</strong>: the model is too complex and learns the noise. It does very well on training data and clearly worse on test data.</li>
</ul>
<p>The diagnosis is simple: compute the score on both parts and compare them.</p>
<pre><code>from sklearn.tree import DecisionTreeRegressor

deep = DecisionTreeRegressor(random_state=0).fit(X_train, y_train)
print(deep.score(X_train, y_train))
print(deep.score(X_test, y_test))</code></pre>
<p>This sketch assumes that you already have <code>X_train</code> and the other parts from a split. An unlimited regression tree gives an R squared of exactly 1.0 on its own training data (unless identical rows have different labels), while the test score is usually much lower. A big gap between the two numbers is the signature of overfitting. The numbers depend on your data, so we do not print them here.</p>

<h3>Fighting overfitting</h3>
<ol>
<li>Get more data, which is often the best cure.</li>
<li>Use a simpler model or limit its complexity (tree depth, fewer features, stronger regularisation).</li>
<li>Use ensembles such as random forests.</li>
<li>Remove noisy or useless features.</li>
<li>Check by cross-validation, the subject of the next lesson.</li>
</ol>

<h3>Fighting underfitting</h3>
<p>Use a more flexible model, add informative features (for example the square of a feature) or reduce regularisation. If both scores are poor, the features may simply not contain the answer, and no model will fix that.</p>

<h3>Try it yourself</h3>
<ol>
<li>Compute MAE, MSE and RMSE by hand for actuals [10, 20] and predictions [12, 17], then check with scikit-learn (MAE 2.5, MSE 6.5).</li>
<li>Fit a linear regression on 8 invented points and print the R squared on the training data.</li>
<li>Train an unlimited tree and a tree with <code>max_depth=2</code> on the same data and compare train and test scores.</li>
<li>Write down in one sentence whether each tree looks over- or underfitted and why.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>MAE is the average absolute error, MSE squares errors, RMSE returns to the original units.</li>
<li>R squared compares the model with always predicting the mean: 1 is perfect and 0 is no better.</li>
<li>Overfitting means good training scores and poor test scores; underfitting means poor on both.</li>
<li>Report errors in the units your audience understands.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Cross-validation, tuning and pipelines',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to estimate a model's performance more reliably with cross-validation, tune settings without touching the test set and build a pipeline that prevents data leakage. A single train-test split can be lucky or unlucky, especially with small data, so we repeat the test several times.</p>

<h3>k-fold cross-validation</h3>
<p>In <strong>k-fold cross-validation</strong> the training data is cut into k equal parts, called folds. The model is trained k times; each time one fold is held back for testing and the other k - 1 folds are used for training. You then average the k scores, and you also look at how much they vary. Every sample gets to be in a test fold exactly once.</p>
<pre><code>import numpy as np
from sklearn.model_selection import KFold

data = np.arange(6)
kf = KFold(n_splits=3)
for train_idx, test_idx in kf.split(data):
    print("test:", test_idx)</code></pre>
<pre>test: [0 1]
test: [2 3]
test: [4 5]</pre>
<p>Without shuffling, the folds are consecutive blocks. If your rows are sorted (say by date or by class) use <code>shuffle=True</code>, or for classification use <code>StratifiedKFold</code>, which keeps class proportions in every fold. The shortcut <code>cross_val_score</code> does the whole job:</p>
<pre><code>from sklearn.model_selection import cross_val_score
from sklearn.tree import DecisionTreeClassifier

scores = cross_val_score(DecisionTreeClassifier(max_depth=3, random_state=0),
                         X_train, y_train, cv=5)
print(scores.mean(), scores.std())</code></pre>
<p>This gives five scores, one per fold. A mean of 0.82 with a spread of 0.02 is trustworthy; a mean of 0.82 with a spread of 0.15 means the score depends heavily on which rows were chosen.</p>

<h3>Tuning settings: grid search</h3>
<p>Settings you choose before training, such as the tree depth or the k in k-NN, are called <strong>hyperparameters</strong>. <code>GridSearchCV</code> tries every combination you list and scores each with cross-validation on the training data only.</p>
<pre><code>from sklearn.model_selection import GridSearchCV
from sklearn.neighbors import KNeighborsClassifier

grid = GridSearchCV(KNeighborsClassifier(),
                    param_grid={"n_neighbors": [1, 3, 5, 7, 9]},
                    cv=5)
grid.fit(X_train, y_train)
print(grid.best_params_)
print(grid.best_score_)</code></pre>
<p>The best setting is then used by <code>grid.predict</code>. Only after tuning do you open the test set, once, to report the final result. Tuning on the test set would quietly make your result too optimistic.</p>

<h3>Pipelines: preventing leakage</h3>
<p>If you scale all the data and then split it, the scaler has already seen the test rows and information has leaked. A <strong>Pipeline</strong> chains the steps so that each one is fitted on the training part only, inside every cross-validation fold.</p>
<pre><code>from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler

pipe = Pipeline([
    ("scale", StandardScaler()),
    ("model", KNeighborsClassifier(n_neighbors=5)),
])
pipe.fit(X_train, y_train)
print(pipe.score(X_test, y_test))</code></pre>
<p>You treat the pipeline like one model: <code>fit</code>, <code>predict</code> and <code>score</code> run all steps in order. You can pass a pipeline to <code>cross_val_score</code> or <code>GridSearchCV</code>, and name its settings with a double underscore, for example <code>"model__n_neighbors"</code>.</p>

<h3>The honest routine</h3>
<ol>
<li>Split off a test set and put it away.</li>
<li>Use cross-validation on the training set to compare models and tune settings.</li>
<li>Choose the final model, refit it on all the training data and test it once.</li>
<li>Report the test score together with the baseline and the spread from cross-validation.</li>
</ol>

<h3>Try it yourself</h3>
<ol>
<li>Run the KFold example with 6 samples and 3 folds, then with <code>n_splits=2</code>. Predict the folds first.</li>
<li>Use the iris data from scikit-learn (<code>load_iris</code>) and run <code>cross_val_score</code> on a decision tree with <code>cv=5</code>.</li>
<li>Build a pipeline of scaler plus k-NN and compare its score with k-NN alone.</li>
<li>Write a sentence explaining why the test set is touched only once.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Cross-validation repeats the train-test process k times and averages the scores.</li>
<li>GridSearchCV tunes hyperparameters using cross-validation on the training data only.</li>
<li>Pipelines keep scaling and other preparation inside the training folds and prevent leakage.</li>
<li>Open the test set once, at the very end.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In scikit-learn's confusion matrix for classes 0 and 1, what do the rows represent?
- The predicted classes
* The true classes
- The features
- The folds
= Rows are the actual classes and columns are the predictions.

Q: A test produces TP = 6, FP = 2, FN = 4 and TN = 88. What is the precision?
- 0.60
* 0.75
- 0.94
- 0.88
= Precision is TP / (TP + FP) = 6 / 8 = 0.75. Recall would be 6 / 10 = 0.60.

Q: With the same numbers (TP = 6, FP = 2, FN = 4, TN = 88), what is the recall?
* 0.60
- 0.75
- 0.94
- 0.06
= Recall is TP / (TP + FN) = 6 / 10 = 0.60.

Q: 10 of 1,000 patients are infected. A model says "healthy" for everyone. What are its accuracy and recall for the infected class?
- Accuracy 10 percent, recall 100 percent
- Accuracy 50 percent, recall 50 percent
* Accuracy 99 percent, recall 0 percent
- Accuracy 0 percent, recall 0 percent
= It is right on all 990 healthy patients (99 percent) but finds none of the infected, so recall is 0.

Q: Actual values are 3, 5, 7 and predictions are 2, 5, 9. What is the MAE?
- 0.5
* 1.0
- 1.5
- 3.0
= The absolute errors are 1, 0 and 2, so the mean is 3 / 3 = 1.0.

Q: A model gets 99 percent on its training data and 70 percent on the test data. What is the most likely problem?
- Underfitting
* Overfitting
- The test set is too large
- Perfect generalisation
= A large gap between training and test performance is the signature of overfitting.

Q: Why is k-fold cross-validation more reliable than one single train-test split?
- It uses fewer rows
* Each sample is tested once and the average of several scores reduces the effect of a lucky or unlucky split
- It removes the need for a test set entirely
- It makes the model more complex
= Averaging over several folds gives a steadier estimate and shows how much the score varies.

Q: Why use a Pipeline that contains the scaler and the model?
- To make the model larger
- Because scalers cannot be used alone
* So the scaler is fitted only on training data in each fold, preventing information leaking from test data
- To avoid using a test set
= A pipeline keeps all preparation steps inside the training part of each split.
QUIZ,
        ],
        [
            'title' => 'Module 5: A full project, responsibility and deployment',
            'summary' => 'Put everything together in one project, learn where bias and harm come from and see how a trained model is saved, served and monitored.',
            'lessons' => [
                [
                    'title' => 'A complete scikit-learn project',
                    'minutes' => 20,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to run the whole workflow on a real dataset, from loading the data to a reported result. We use the famous iris flower data that ships inside scikit-learn, because it needs no download and is small enough to check, and then show how to swap in a CSV file of your own.</p>

<h3>Step 1: the question and the data</h3>
<p>The question: can we tell which of three iris species a flower belongs to from four measurements of its petals and sepals (in centimetres)? This is classification with three classes.</p>
<pre><code>from sklearn.datasets import load_iris

iris = load_iris(as_frame=True)
X = iris.data
y = iris.target

print(X.shape)
print(list(iris.target_names))
print(y.value_counts().sort_index().tolist())</code></pre>
<pre>(150, 4)
['setosa', 'versicolor', 'virginica']
[50, 50, 50]</pre>
<p>There are 150 flowers, four features and three balanced classes with 50 each. Because the classes are balanced, accuracy is a fair measure and the baseline (always guessing one class) is one third, about 0.33.</p>

<h3>Step 2: look before you model</h3>
<pre><code>print(X.isna().sum().sum())
print(X.describe().loc[["min", "max"]].round(1))</code></pre>
<p>The first line counts missing cells (the iris data has none, so it prints 0). The second shows the smallest and largest value of each feature, a quick way to spot impossible numbers. In your own data this is the moment to look for negative ages, prices of zero and typing mistakes.</p>

<h3>Step 3: split first</h3>
<pre><code>from sklearn.model_selection import train_test_split

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)
print(X_train.shape, X_test.shape)
print(y_test.value_counts().sort_index().tolist())</code></pre>
<pre>(120, 4) (30, 4)
[10, 10, 10]</pre>
<p>Stratifying gives exactly 10 test flowers per species.</p>

<h3>Step 4: compare candidates with cross-validation</h3>
<pre><code>from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler
from sklearn.linear_model import LogisticRegression
from sklearn.neighbors import KNeighborsClassifier
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import cross_val_score

candidates = {
    "logistic": Pipeline([("scale", StandardScaler()),
                          ("model", LogisticRegression(max_iter=1000))]),
    "knn":      Pipeline([("scale", StandardScaler()),
                          ("model", KNeighborsClassifier(n_neighbors=5))]),
    "forest":   RandomForestClassifier(n_estimators=200, random_state=0),
}

for name, model in candidates.items():
    scores = cross_val_score(model, X_train, y_train, cv=5)
    print(name, round(scores.mean(), 3))</code></pre>
<p>Run this yourself. All three models usually score about 0.93 to 0.98 on this easy dataset, which is far above the 0.33 baseline, and the differences between them are small compared with the fold-to-fold spread. When models are this close, prefer the simpler or more explainable one. Your numbers can differ slightly with your library version.</p>

<h3>Step 5: final test, once</h3>
<pre><code>from sklearn.metrics import classification_report, confusion_matrix

final = candidates["logistic"]
final.fit(X_train, y_train)
pred = final.predict(X_test)

print(confusion_matrix(y_test, pred))
print(classification_report(y_test, pred,
                            target_names=iris.target_names))</code></pre>
<p>The confusion matrix has three rows and three columns. Almost all counts sit on the diagonal (correct predictions), and a mistake, if any, is usually between versicolor and virginica, which resemble each other more than they resemble setosa. The report adds precision, recall and F1 per species.</p>

<h3>Step 6: report honestly</h3>
<p>A good report states the question, the data (size, source, date), the baseline, the cross-validation scores with their spread, the single final test score, the kinds of mistake the model makes, and the limits: for example, "tested only on these 150 flowers, measured in one place; it may not work on other varieties".</p>

<h3>Using your own CSV file</h3>
<p>To adapt the project, replace Step 1 with <code>df = pd.read_csv("mydata.csv")</code>, choose the label column, split X and y, and add preparation to the pipeline. A <code>ColumnTransformer</code> lets you scale the number columns and one-hot encode the category columns inside the same pipeline, so that the preparation is learned from training data only.</p>

<h3>Try it yourself</h3>
<ol>
<li>Run steps 1 to 3 and confirm the shapes (150, 4), (120, 4) and (30, 4).</li>
<li>Run step 4 and write down the three cross-validation means.</li>
<li>Fit the best model once on the training set and print its test accuracy and confusion matrix.</li>
<li>Write a five-sentence report with the baseline, the score and one limitation.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A project follows: question, inspect, split, compare with cross-validation, test once, report.</li>
<li>Stratified splits keep class proportions; pipelines keep preparation inside the training data.</li>
<li>When models score alike, choose the simpler one.</li>
<li>Report the baseline, the spread and the limits, not just one good number.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Bias, fairness and privacy in machine learning',
                    'minutes' => 17,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to spot common sources of bias and privacy risk in an ML project and take practical steps to reduce harm. Models are used to decide who gets a loan, a job interview or a place in a programme, so mistakes land on real people, often those with the least power to complain.</p>

<h3>Where bias comes from</h3>
<ul>
<li><strong>Unrepresentative data.</strong> A face detection model trained mostly on light-skinned faces works worse on dark-skinned ones. A crop-disease model trained on photos from one district may fail elsewhere. A speech model trained mostly on one accent understands others poorly.</li>
<li><strong>Historical bias.</strong> If past hiring decisions favoured men, a model trained to copy them will favour men too, because it learns the pattern in the labels. The model is not neutral; it reproduces yesterday's decisions with a mathematical look.</li>
<li><strong>Measurement bias.</strong> The label may be a poor stand-in for what we really care about. "Arrested" is not the same as "committed a crime", and "number of clinic visits" is not the same as "health need".</li>
<li><strong>Proxy features.</strong> Even if you remove a sensitive column such as gender, other columns (home area, school, first name) can encode it, so the bias remains.</li>
<li><strong>Feedback loops.</strong> If a model decides where police patrol or whom a lender approves, the new data it collects is shaped by its own decisions.</li>
</ul>

<h3>Checking for unfair results</h3>
<p>Overall accuracy can look good while one group suffers. Always break the evaluation down by group (gender, region, age band, language) and compare error rates. In pandas:</p>
<pre><code>import pandas as pd

results = pd.DataFrame({
    "group":  ["A", "A", "A", "A", "B", "B", "B", "B"],
    "actual": [1, 0, 1, 0, 1, 1, 0, 0],
    "pred":   [1, 0, 1, 0, 0, 0, 0, 1],
})
results["correct"] = results["actual"] == results["pred"]
print(results.groupby("group")["correct"].mean())</code></pre>
<pre>group
A    1.00
B    0.25
Name: correct, dtype: float64</pre>
<p>Overall accuracy is 62.5 percent, but group A gets 100 percent while group B gets only 25 percent. The overall number hides the gap completely. Also compare recall per group: if the model misses sick people in one region more often than in another, it is unfair in a way that matters.</p>
<p>Note that fairness has several mathematical definitions, such as equal error rates or equal approval rates, and they cannot all be satisfied at once. Deciding which one is appropriate is a human and social decision, not a technical one.</p>

<h3>Reducing the harm</h3>
<ol>
<li>Ask who is in the data and who is missing. Collect more examples from under-represented groups, with their consent.</li>
<li>Check what the label really measures.</li>
<li>Test per group before deployment and after.</li>
<li>Keep a human in the loop for decisions that change people's lives, and give people a way to challenge a decision.</li>
<li>Document the model: its purpose, data, known limits and results per group (often called a <strong>model card</strong>).</li>
<li>Do not use ML when a simple, transparent rule would do, or when the stakes are high and the data is weak.</li>
</ol>

<h3>Privacy and consent</h3>
<p>Training data often contains personal information such as names, phone numbers, health records or location. In Uganda the <strong>Data Protection and Privacy Act, 2019</strong> sets rules for collecting and using personal data, and similar laws exist in other East African countries. As a practitioner you should collect only what you need, tell people why you need it, remove names and numbers that identify people, store the data securely and delete it when it is no longer required. Never upload private data about real people to online tools you do not control. Remember that a model can sometimes reveal details of its training data, and that removing names alone does not always make data anonymous, because a combination of age, village and job can identify one person.</p>

<h3>Explainability and honesty</h3>
<p>People affected by a model deserve to know that a model is being used and, in simple terms, how it reaches decisions. Simple models such as linear models and small trees are easier to explain. Never claim a model is "99 percent accurate" without saying on what data, against what baseline and for which groups.</p>

<h3>Try it yourself</h3>
<ol>
<li>Pick a system you know, such as a loan app or a school selection tool, and list two groups who could be treated unfairly.</li>
<li>List three features it might use that act as proxies for sensitive information.</li>
<li>Run the group accuracy example above and compute recall for group B by hand.</li>
<li>Write five lines of a model card for a model you have built in this course.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Bias enters through unrepresentative data, historical decisions, poor labels, proxies and feedback loops.</li>
<li>Evaluate by group, not only overall.</li>
<li>Protect personal data, follow the law and collect only what you need.</li>
<li>Keep humans accountable for decisions and document what your model can and cannot do.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Saving a model and putting it to work',
                    'minutes' => 18,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to save a trained model to a file, load it again, wrap it in a small prediction function and describe common ways of deploying it. A model that lives only in your notebook helps nobody; it becomes useful when an app, a website or a colleague can ask it for predictions.</p>

<h3>Saving and loading with joblib</h3>
<p>Training can take minutes or hours, so you train once and save the result. The <code>joblib</code> library (installed together with scikit-learn) stores the fitted object, which should be a whole <strong>pipeline</strong> so that the scaling and encoding travel with the model.</p>
<pre><code>import joblib
from sklearn.datasets import load_iris
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler
from sklearn.linear_model import LogisticRegression

iris = load_iris()
pipe = Pipeline([("scale", StandardScaler()),
                 ("model", LogisticRegression(max_iter=1000))])
pipe.fit(iris.data, iris.target)

joblib.dump(pipe, "iris-model.joblib")

loaded = joblib.load("iris-model.joblib")
print(loaded.predict([[5.0, 3.4, 1.5, 0.2]]))</code></pre>
<pre>[0]</pre>
<p>The measurements 5.0, 3.4, 1.5 and 0.2 describe a typical setosa flower, which is class 0. Two safety warnings apply. First, <strong>only load model files from sources you trust</strong>: joblib and pickle files can run code when loaded, so a file from a stranger is dangerous. Second, a saved model must be loaded with a similar scikit-learn version to the one that saved it, so record the version number (for example in a <code>requirements.txt</code> file).</p>

<h3>A prediction function</h3>
<p>Wrap the model in a function that checks its input, because real users send bad data.</p>
<pre><code>NAMES = ["setosa", "versicolor", "virginica"]

def predict_species(sepal_len, sepal_wid, petal_len, petal_wid):
    values = [sepal_len, sepal_wid, petal_len, petal_wid]
    if any(v &lt;= 0 or v &gt; 20 for v in values):
        raise ValueError("measurements must be between 0 and 20 cm")
    index = int(loaded.predict([values])[0])
    return NAMES[index]

print(predict_species(5.0, 3.4, 1.5, 0.2))</code></pre>
<pre>setosa</pre>

<h3>Ways to deploy</h3>
<table>
<thead><tr><th>Approach</th><th>How it works</th><th>Good for</th></tr></thead>
<tbody>
<tr><td>Batch</td><td>A script runs daily or weekly and writes predictions to a file or database</td><td>Monthly churn lists, nightly demand forecasts</td></tr>
<tr><td>Web service (API)</td><td>A small Python web app, for example with Flask or FastAPI, loads the model and answers requests with JSON</td><td>Websites, mobile apps and other programs that need an answer on demand</td></tr>
<tr><td>Inside an application</td><td>The model file is bundled with a desktop or mobile app</td><td>Offline use where internet is poor</td></tr>
<tr><td>Notebook or dashboard</td><td>A tool such as a notebook or a simple dashboard lets staff enter values</td><td>Internal analysis, prototypes</td></tr>
</tbody>
</table>
<p>A website written in another language, for instance PHP, can use a Python model by sending the values to a Python web service and reading the JSON answer. This keeps the model in one place and the website simple. When connectivity is a problem, batch predictions or an offline tool may serve users better than a live service.</p>

<h3>Monitoring: the work after launch</h3>
<ul>
<li><strong>Data drift.</strong> The world changes: prices rise, a new mobile money product appears, a drought changes crop patterns. When new inputs differ from the training data, accuracy falls quietly. Compare the average and range of incoming features with those in training.</li>
<li><strong>Track outcomes.</strong> Collect the real results when they become known and recompute precision, recall or error regularly.</li>
<li><strong>Retrain on a schedule</strong> or when performance drops, and version every model so that you can roll back.</li>
<li><strong>Log carefully.</strong> Record inputs and predictions to investigate problems, but protect any personal information they contain.</li>
<li><strong>Keep a fallback.</strong> Decide what happens when the model service is down or unsure, for example sending the case to a person.</li>
</ul>

<h3>Try it yourself</h3>
<ol>
<li>Train the pipeline above, save it with joblib and load it in a fresh notebook session.</li>
<li>Run the setosa measurements and then a flower with 6.5, 3.0, 5.5 and 2.0 (very likely virginica).</li>
<li>Add a check to the function that rejects missing values.</li>
<li>List three things you would monitor if the model were used in a real farm advice service.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Save the whole pipeline with joblib and record library versions; load only trusted files.</li>
<li>Wrap the model in a function that validates input.</li>
<li>Deployment can be batch, a web service, inside an app or a notebook, depending on the users and connectivity.</li>
<li>After launch, watch for data drift, track real outcomes and plan retraining.</li>
</ul>
HTML,
                ],
                [
                    'title' => 'Where to go next',
                    'minutes' => 14,
                    'body' => <<<'HTML'
<p>After this lesson you will be able to describe what lies beyond this course and plan your next months of learning. You now know the core of classic machine learning on tables of data. The field is large, and a clear map helps you choose wisely instead of chasing every new tool.</p>

<h3>What you can do now</h3>
<ul>
<li>Frame a problem as regression, classification or clustering and choose a baseline.</li>
<li>Prepare data and split it honestly, using pipelines to avoid leakage.</li>
<li>Train, compare and tune linear models, trees, forests and nearest-neighbour models.</li>
<li>Report results with the right measures and with their limits.</li>
<li>Identify bias and privacy risks and speak about them clearly.</li>
</ul>

<h3>Topics to learn next</h3>
<table>
<thead><tr><th>Topic</th><th>What it adds</th></tr></thead>
<tbody>
<tr><td>Feature engineering</td><td>Creating better columns from raw data (dates, text counts, ratios). It often improves a model more than changing the algorithm.</td></tr>
<tr><td>Gradient boosting</td><td>Strong tree ensembles, including scikit-learn's own versions and the libraries XGBoost and LightGBM.</td></tr>
<tr><td>Time series</td><td>Forecasting when the order of rows matters, with special ways of splitting data by time.</td></tr>
<tr><td>Text and language</td><td>Turning words into numbers to classify messages or reviews, including local languages.</td></tr>
<tr><td>Deep learning</td><td>Neural networks for images, sound and language, using libraries such as PyTorch or TensorFlow.</td></tr>
<tr><td>Imbalanced data</td><td>Methods for rare events such as fraud: class weights, resampling and suitable measures.</td></tr>
<tr><td>MLOps</td><td>Automating training, testing, deployment and monitoring.</td></tr>
</tbody>
</table>

<h3>Building a portfolio</h3>
<p>Employers and clients want evidence. Build three small projects on problems from your own environment, and publish each as a short write-up with the code. Good ideas include predicting maize prices from weather data, classifying SMS messages as scam or not, forecasting boda boda demand by day, or grouping school pupils by their strengths. Use open data from government or international portals where you can, and never publish personal data.</p>
<p>For each project write: the question, the data and its source, the baseline, the method, the results with honest limits and what you would do next. A clear write-up with a modest result impresses more than a hidden notebook with a perfect score that nobody can reproduce.</p>

<h3>Learning habits that work</h3>
<ol>
<li><strong>Work on real problems.</strong> Choose a question that you care about and let the technique follow.</li>
<li><strong>Read the scikit-learn documentation.</strong> Its examples are clear, and reading them is a skill in itself.</li>
<li><strong>Join a community.</strong> Local developer meetups, university clubs and online forums offer feedback. Share your work and ask for review.</li>
<li><strong>Revise the basics.</strong> Statistics, probability and linear algebra make every method easier to understand.</li>
<li><strong>Keep ethics in the room.</strong> Ask who might be harmed before you ask how accurate the model is.</li>
</ol>

<h3>A note on AI hype</h3>
<p>You will hear that artificial intelligence will do everything. Your training lets you answer calmly: models are tools that learn patterns from data, they make mistakes, and they need people who understand data, context and responsibility. Many valuable problems in Africa need exactly these careful, modest, well-tested solutions rather than the largest models.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose one project idea from your community and write the question, the label and three features.</li>
<li>Find an open dataset that could answer it, and note the source and its licence.</li>
<li>Plan the steps of the workflow for it in eight lines.</li>
<li>Decide the first topic from the table that you will learn next, and set a date.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>You know the core workflow of classic machine learning on tables.</li>
<li>Feature engineering, boosting, time series, text and deep learning are the natural next steps.</li>
<li>A portfolio of honest, documented projects is the best proof of skill.</li>
<li>Responsible practice is part of the skill, not an extra.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Which are common sources of bias in a machine learning project? (choose all that apply)
* Training data that does not represent all groups
* Labels that copy past unfair decisions
- Using a pipeline
- Splitting data into train and test sets
= Unrepresentative data and biased historical labels both teach the model unfair patterns. Pipelines and splits are good practice.

Q: A team removes the gender column but the model still treats men and women differently. What is the most likely explanation?
- The model has a bug in fit
* Other columns act as proxies for gender
- The test set was too large
- Standardisation was skipped
= Home area, school or other features can encode the sensitive information.

Q: A model has 62 percent accuracy overall. Group A gets 100 percent and group B gets 25 percent. What should you do?
- Report only the overall accuracy
* Report results per group and investigate why group B is served so badly
- Delete group B from the test set
- Increase k
= Overall numbers hide unfair gaps. Break results down by group.

Q: Which practice is best for personal data used in training?
- Collect everything you can, just in case
- Upload it to any free online tool
* Collect only what you need, protect it and remove identifying details
- Keep it forever
= Data minimisation and security are core privacy habits and are expected by data protection laws.

Q: Why should you save a whole Pipeline rather than just the final model?
- Pipelines are smaller
* The scaling and encoding steps must be applied in the same way to new data
- Models cannot be saved alone
- A pipeline guarantees 100 percent accuracy
= A prediction on new data needs the same preparation that the training data received.

Q: Why must you only load joblib or pickle model files from trusted sources?
- They are too large
* Loading them can run code, so a malicious file could harm your computer
- They only work on Linux
- They delete the training data
= These formats can execute code during loading.

Q: Several months after launch, a model's accuracy quietly drops because customers' behaviour has changed. What is this called?
- Underfitting
- Stratification
* Data drift
- Cross-validation
= When live data differs from the training data, performance can fall. That is why monitoring and retraining matter.

Q: A website written in PHP wants to use a Python model. Which is a sensible approach?
- Rewrite scikit-learn in PHP
- Email the data to the model's author each time
* Run the model in a small Python web service that returns JSON, and call it from the website
- Copy the model file into the browser
= A web service keeps the model in one place and lets other languages call it over HTTP.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: Which of these is a classification problem?
- Predicting tomorrow's temperature in degrees
* Deciding whether a message is spam or not spam
- Predicting the price of a plot of land
- Estimating how many litres of milk a farm will produce
= Spam or not spam is a category. The others predict numbers, so they are regression.

Q: What is a feature in machine learning?
- The answer the model must predict
* A measured input about each sample, such as age or size
- The final accuracy score
- A kind of chart
= Features are the input columns. The label is the answer.

Q: A telecom company has customer records with no labels and wants to discover types of customers. Which technique fits best?
- Linear regression
- Logistic regression
- A random forest classifier
* k-means clustering
= Without labels the job is unsupervised, and clustering groups similar customers.

Q: What is the usual three-step pattern for a scikit-learn model?
- Scale, delete, save
* Create the model, fit it on training data, predict on new data
- Predict, then fit, then create
- Plot, fit, report
= The same pattern works for nearly all scikit-learn estimators.

Q: Why is median often used to fill missing numeric values?
- It is always the largest value
* It is the middle value and is not pulled around by extreme numbers
- It deletes the row
- It converts text to numbers
= The median is robust against outliers.

Q: In one-hot encoding of the column town with values Gulu and Mbale, what do you get?
- A single column with 1 and 2
- A single column with the town length
* One column per town, holding True or False (or 1 or 0)
- The towns sorted alphabetically
= One-hot encoding creates a separate indicator column for each category and creates no false order.

Q: With test_size=0.25, how many of 20 rows go to the test set?
- 2
- 4
* 5
- 15
= 25 percent of 20 is 5.

Q: 70 percent of a dataset belongs to class A. What does a model that always predicts A score in accuracy?
- 30 percent
- 50 percent
* 70 percent
- 100 percent
= It is right exactly when the true class is A, which is 70 percent of the time. This is the baseline.

Q: A line y = 3 + 4x is fitted. What is the predicted y when x is 2?
- 9
* 11
- 14
- 24
= 3 plus 4 times 2 is 11.

Q: What does the residual of a regression prediction measure?
- The number of features
* The gap between the actual value and the predicted value
- The learning rate
- The size of the test set
= A residual is actual minus predicted.

Q: What range of values does the sigmoid function in logistic regression produce?
- Any number
- Only whole numbers
* Between 0 and 1
- Between -1 and 0
= The sigmoid squeezes the linear score into a probability between 0 and 1.

Q: What is the effect of lowering the probability threshold from 0.5 to 0.3 for the positive class?
- Fewer positive predictions, higher precision usually
* More positive predictions, usually higher recall and lower precision
- The model becomes unsupervised
- No change at all
= A lower bar flags more cases, finding more real positives but also raising false alarms.

Q: In k-nearest neighbours with k = 5, three of the nearest neighbours are class 1 and two are class 0. What is predicted?
- 0
* 1
- 0.5
- Nothing
= Majority vote of the five nearest neighbours gives class 1.

Q: Why do distance-based models such as k-NN and k-means need scaled features?
* Features with large numeric ranges would dominate the distance
- They cannot store small numbers
- Scaling makes the data smaller on disk
- Scaling creates the labels
= Without scaling, a feature like income in UGX overwhelms a feature like age.

Q: What is Gini impurity of a group that contains 50 percent of each of two classes?
- 0
* 0.5
- 1
- 2
= One minus 0.25 minus 0.25 equals 0.5, the highest impurity for two classes.

Q: Which of these helps to reduce overfitting in a decision tree? (choose all that apply)
* Limiting the maximum depth
* Requiring a larger minimum leaf size
- Allowing unlimited depth
- Training on the test set
= Simpler trees memorise less. Unlimited depth and training on the test set make things worse.

Q: What are the two sources of randomness that make the trees in a random forest different from each other?
- Different test sets and different labels
* Bootstrap samples of rows and random subsets of features at each split
- Different scalers and different units
- Different file names
= Bagging and random feature choice make the trees diverse so averaging helps.

Q: In a confusion matrix produced by scikit-learn for classes 0 and 1, what is the entry in row 1, column 0?
- True positives
- False positives
* False negatives
- True negatives
= Row 1 is actually positive and column 0 is predicted negative: a missed case.

Q: TP = 30, FP = 10, FN = 20, TN = 940. What is the recall?
- 0.75
* 0.60
- 0.97
- 0.03
= Recall is TP / (TP + FN) = 30 / 50 = 0.60. Precision is 30 / 40 = 0.75.

Q: For a screening test for a dangerous disease, which goal is usually most important?
- High precision at any cost
* High recall, so that few sick people are missed
- High accuracy only
- Using the largest model
= Missing a real case is costly, so recall matters most, and some false alarms are accepted.

Q: Actual values are 10 and 20 and predictions are 12 and 17. What is the MAE?
- 1.5
- 2.0
* 2.5
- 6.5
= The absolute errors are 2 and 3, and their mean is 2.5. The MSE is (4 + 9) / 2 = 6.5.

Q: A model scores R squared 0 on the test set. What does this mean?
- It is perfect
- It predicts everything wrongly by a huge amount
* It is no better than always predicting the mean of the labels
- It is overfitted
= R squared of 0 means the model explains none of the variation beyond the mean.

Q: Training accuracy 100 percent, test accuracy 72 percent. What is the diagnosis?
- Underfitting
* Overfitting
- Perfect generalisation
- Data too clean
= A large gap between training and test performance indicates overfitting.

Q: Why is cross-validation better than a single split for comparing models on a small dataset?
- It trains on fewer rows
* It averages several test scores, so luck in one split matters less
- It removes the need to scale features
- It always gives higher scores
= Averaging over folds gives a steadier estimate and shows its spread.

Q: When should you look at the test set to report a result?
- After every change to the model
* Once, at the very end, after the model and its settings are chosen
- Before training
- Never
= Repeated looks at the test set make your estimate too optimistic.

Q: In GridSearchCV, which data is used to score each combination of settings?
- The test set
* The training data, by cross-validation
- The labels only
- No data, it is random
= Tuning must not use the test set, so the scoring is done by cross-validation on training data.

Q: You scale all the data, then split it into train and test. What is the problem?
- None, this is the recommended order
* Information from the test rows leaked into the scaler, so the evaluation is too optimistic
- The scaler will refuse to run
- The labels are lost
= Split first and fit the scaler on training data, ideally inside a Pipeline.

Q: A recruitment model trained on past hiring data favours men. What is the most likely reason?
- The model dislikes women
* It learned the pattern of historical decisions in the labels
- scikit-learn has a built-in bias
- The model was too simple
= Historical bias in the data is reproduced by the model.

Q: Which saved-model practice is correct?
- Load a model file you received from an unknown person
* Save the whole pipeline, record library versions and load only trusted files
- Save only the scaler
- Never record versions
= Model files can run code and need compatible versions, and the preparation steps must travel with the model.

Q: Which is the best description of data drift?
- The model gets larger over time
* The incoming data changes compared with the training data, so performance may quietly fall
- The test set becomes the training set
- The labels are deleted
= Monitoring and retraining are needed because the world changes.
QUIZ,
];
