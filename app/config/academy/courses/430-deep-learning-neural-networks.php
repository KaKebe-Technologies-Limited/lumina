<?php
return [
    'slug'        => 'deep-learning-neural-networks',
    'title'       => 'Deep Learning and Neural Networks Explained',
    'category'    => 'ai',
    'level'       => 'advanced',
    'icon'        => 'layers',
    'sort'        => 430,
    'summary'     => 'Understand how neural networks learn, from neurons and gradient descent to CNNs and transformers, then train, evaluate and deploy a small image or text classifier in a free Python notebook.',
    'description' => '<p>Deep learning powers the tools many of us use every day: phone cameras that find faces, voice typing, translation and AI chat assistants. Behind the mystery is a small set of ideas: simple units that multiply and add numbers, a score that says how wrong the answer is, and a method that nudges thousands of numbers in the right direction until the answer improves.</p><p>This advanced course explains those ideas in plain words with small worked numbers you can follow with a calculator. You will learn why networks overfit, how to split and prepare data, how to judge a model with accuracy, precision and recall, and how convolutional networks, attention and transformers fit together. You will then run real experiments in a free Python notebook, reuse a pretrained model through transfer learning, think about cost and energy, and deploy a small model.</p><p>The course ends with a capstone: you build, test and document your own image or text classifier on a problem from your community, school or business.</p>',
    'outcomes'    => [
        'Explain how a neuron, a layer, weights, biases and activation functions combine to make a network',
        'Calculate a forward pass, a loss value and one gradient descent step by hand on a tiny example',
        'Describe the difference between training and inference and how epochs, batches and learning rate fit in',
        'Detect overfitting with a validation set and reduce it with regularisation, dropout and early stopping',
        'Prepare, split and label data properly and avoid data leakage',
        'Evaluate a classifier with a confusion matrix, accuracy, precision, recall and F1 score',
        'Explain convolutional networks, attention, tokens and transformers, and how large language models are built',
        'Train and fine-tune a small model with Keras in a Google Colab notebook, using transfer learning',
        'Estimate the compute, cost and energy of a project and deploy a small model responsibly',
    ],
    'requirements' => ['Completed Introduction to Machine Learning (machine-learning-introduction)', 'Completed Introduction to Programming with Python (python-programming-intro)', 'A computer with internet access and a free Google account for the notebook activities', 'Comfortable with basic school mathematics: multiplying, adding, averages and percentages'],
    'pass_mark'   => 70,
    'resources'   => [
        ['title' => 'Keras: official documentation and guides', 'url' => 'https://keras.io/'],
        ['title' => 'PyTorch: official tutorials', 'url' => 'https://pytorch.org/tutorials/'],
        ['title' => 'TensorFlow: official site', 'url' => 'https://www.tensorflow.org/'],
        ['title' => 'Google Colab: free notebooks in the browser', 'url' => 'https://colab.research.google.com/'],
        ['title' => 'Dive into Deep Learning (free online book)', 'url' => 'https://d2l.ai/'],
    ],
    'modules' => [
        // ---------------------------------------------------------------- MODULE 1
        [
            'title'   => 'Module 1: How a neural network works',
            'summary' => 'Build the core ideas with small numbers: neurons, activations, loss, gradient descent and the training loop.',
            'lessons' => [
                [
                    'title'   => 'Neurons, weights and layers',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate by hand what one artificial neuron does and explain how layers of neurons make a network. This matters because every deep learning system, from a phone camera to a chatbot, is built from this one simple operation repeated millions of times.</p>

<h3>The artificial neuron</h3>
<p>An <strong>artificial neuron</strong> is a tiny calculator. It receives some numbers (the <strong>inputs</strong>), multiplies each by a number called a <strong>weight</strong>, adds the results together, adds one more number called the <strong>bias</strong>, and gives a single number out. The weight says how much an input matters. The bias shifts the result up or down, a bit like a starting score.</p>
<pre><code>output = (x1 * w1) + (x2 * w2) + bias</code></pre>

<h3>A worked example</h3>
<p>Suppose a small shop wants to guess a score for how good a day will be. Input x1 is the number of customers expected (3, in hundreds) and x2 is the number of rainy hours (2). The network has learned weights w1 = 0.4 and w2 = -0.5, and bias = 0.2.</p>
<pre><code>output = (3 * 0.4) + (2 * -0.5) + 0.2
       = 1.2 - 1.0 + 0.2
       = 0.4</code></pre>
<p>The positive weight on customers pushes the score up. The negative weight on rain pulls it down. Training is simply the process of finding good weights and biases.</p>

<blockquote><p><strong>Quick check:</strong> A neuron has inputs 2 and 5, weights 1 and 0.5, and bias -1. What is its output?</p><p>Answer: 3.5, because (2 x 1) + (5 x 0.5) + (-1) = 2 + 2.5 - 1 = 3.5.</p></blockquote>

<h3>From one neuron to a layer</h3>
<p>A <strong>layer</strong> is a group of neurons that all look at the same inputs, each with its own weights. A <strong>network</strong> stacks layers: the output numbers of one layer become the inputs of the next.</p>
<table>
<thead><tr><th>Layer type</th><th>Job</th></tr></thead>
<tbody>
<tr><td>Input layer</td><td>Holds the raw numbers, such as pixel values or measurements</td></tr>
<tr><td>Hidden layers</td><td>Combine earlier numbers into more useful features</td></tr>
<tr><td>Output layer</td><td>Gives the final answer, such as a class or a price</td></tr>
</tbody>
</table>
<p>A network is called <strong>deep</strong> when it has many hidden layers. Early layers find simple patterns; later layers combine them into richer ones.</p>

<h3>Counting parameters</h3>
<p>The weights and biases together are the <strong>parameters</strong>. A layer with 4 inputs and 3 neurons has 4 x 3 = 12 weights plus 3 biases, so 15 parameters. Modern language models have billions of them, which is why they need so much memory.</p>

<blockquote><p><strong>Quick check:</strong> How many parameters does a layer with 10 inputs and 5 neurons have?</p><p>Answer: 55, because there are 10 x 5 = 50 weights plus 5 biases.</p></blockquote>

<h3>Scenario</h3>
<p>A teacher in Jinja wants to predict whether a learner will pass using three inputs: attendance, homework average and a mock test mark. A network with one hidden layer of 4 neurons has (3 x 4) + 4 = 16 parameters in the hidden layer and (4 x 1) + 1 = 5 in the output layer, 21 in all. All 21 start as random numbers; training adjusts them until predictions match the real results.</p>

<h3>Try it yourself</h3>
<ol>
<li>Choose two inputs of your own, such as hours studied and hours slept.</li>
<li>Invent a weight for each and a bias, then calculate the neuron output on paper.</li>
<li>Change one weight to a negative number and recalculate. Notice the effect.</li>
<li>Work out how many parameters a layer with 6 inputs and 8 neurons has.</li>
<li>Check your arithmetic in a Python cell with <code>print(2*1 + 5*0.5 - 1)</code> if you have a notebook open.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A neuron multiplies inputs by weights, adds them and adds a bias.</li>
<li>Layers of neurons feed into each other; many hidden layers make a network deep.</li>
<li>Parameters are all the weights and biases; learning means changing them.</li>
<li>Parameters in a dense layer = inputs x neurons + neurons.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Activation functions and the forward pass',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain why networks need activation functions and carry numbers forward through a small network by hand. This matters because without activations, a hundred layers would collapse into one simple formula and the network could not learn complex patterns.</p>

<h3>Why a straight line is not enough</h3>
<p>If every neuron only multiplies and adds, then stacking layers still gives a straight-line relationship, just with different numbers. Real problems, such as recognising a handwritten 7, are not straight lines. An <strong>activation function</strong> is a small bending step applied to each neuron's number before it moves on. It lets the network model curves and complicated shapes.</p>

<h3>Common activation functions</h3>
<table>
<thead><tr><th>Name</th><th>Rule</th><th>Typical use</th></tr></thead>
<tbody>
<tr><td>ReLU</td><td>If the number is negative, output 0; otherwise keep it</td><td>Hidden layers (the usual default)</td></tr>
<tr><td>Sigmoid</td><td>Squashes any number into a value between 0 and 1</td><td>Output for yes/no problems</td></tr>
<tr><td>Softmax</td><td>Turns a list of numbers into probabilities that add up to 1</td><td>Output for choosing one of many classes</td></tr>
</tbody>
</table>
<p>Examples: ReLU(0.4) = 0.4 and ReLU(-2) = 0. Sigmoid(0) = 0.5, sigmoid(2) is about 0.88 and sigmoid(-2) is about 0.12.</p>

<blockquote><p><strong>Quick check:</strong> What does ReLU output for the inputs 3, -1 and 0?</p><p>Answer: 3, 0 and 0, because ReLU keeps positive numbers and turns negative numbers into zero.</p></blockquote>

<h3>The forward pass</h3>
<p>Sending inputs through the network to get an output is called the <strong>forward pass</strong>. Here is a tiny network with one input, one hidden neuron (ReLU) and one output neuron.</p>
<pre><code>x = 2
hidden: z1 = (2 * 1.5) + (-1) = 2     -> ReLU(2) = 2
output: z2 = (2 * 0.5) + 0.2 = 1.2    -> prediction = 1.2</code></pre>
<p>If instead x = 0.2, then z1 = 0.3 - 1 = -0.7, ReLU gives 0, and the output is 0 x 0.5 + 0.2 = 0.2. The hidden neuron has "switched off", which is exactly the bending effect we wanted.</p>

<h3>Softmax in a small example</h3>
<p>Suppose a network scores three crop diseases as 2, 1 and 0. Softmax first takes e raised to each score (about 7.39, 2.72 and 1.00), adds them (11.11) and divides:</p>
<pre><code>probabilities = 0.665, 0.245, 0.090   (they add up to 1)</code></pre>
<p>The model says it is about 66 percent sure of the first disease. Remember that a probability from softmax is a confidence score, not a guarantee.</p>

<blockquote><p><strong>Quick check:</strong> Which activation would you put on the last layer of a model that picks one of five animal classes?</p><p>Answer: Softmax, because it turns five scores into five probabilities that add up to 1.</p></blockquote>

<h3>Scenario</h3>
<p>A student builds a network to predict maize yield and, by mistake, uses no activation in the hidden layers. Accuracy never improves beyond a simple linear regression. After adding ReLU to the hidden layers, the model captures the effect of rainfall that is helpful only up to a point and harmful when it floods. The bending made the difference.</p>

<h3>Try it yourself</h3>
<ol>
<li>In a Python cell write <code>def relu(v): return max(0, v)</code>.</li>
<li>Run <code>print([relu(v) for v in [-3, -0.5, 0, 2, 7]])</code> and check the answers by hand.</li>
<li>Carry x = 3 through the tiny network above and write down the prediction.</li>
<li>Explain in one sentence to a friend why we need activation functions.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Activation functions bend the numbers so the network can learn curved, complex patterns.</li>
<li>ReLU is the common hidden-layer choice; sigmoid and softmax suit outputs.</li>
<li>The forward pass multiplies, adds, applies the activation and passes the result on.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Loss and gradient descent',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to calculate a loss value and perform one gradient descent step by hand. This matters because this is how every neural network learns: measure the mistake, then change the weights a little to make the mistake smaller.</p>

<h3>Loss: a score for being wrong</h3>
<p>The <strong>loss</strong> (or cost) is a single number that says how wrong the model's predictions are. Smaller is better. A common loss for predicting numbers is the squared error: take prediction minus true value, then square it. Squaring makes every error positive and punishes big mistakes more.</p>
<table>
<thead><tr><th>Task</th><th>Usual loss</th></tr></thead>
<tbody>
<tr><td>Predict a number, such as a price</td><td>Mean squared error</td></tr>
<tr><td>Choose between classes</td><td>Cross-entropy (punishes confident wrong answers heavily)</td></tr>
</tbody>
</table>

<h3>Going downhill</h3>
<p>Imagine walking down a foggy hill to reach the valley. You cannot see the bottom, but you can feel which way the ground slopes under your feet. You step downhill, feel again, and repeat. <strong>Gradient descent</strong> does the same with the loss. The <strong>gradient</strong> tells us how the loss changes when we change a weight slightly. We move the weight in the opposite direction, by a step size called the <strong>learning rate</strong>.</p>
<pre><code>new_weight = old_weight - learning_rate * gradient</code></pre>

<h3>A full worked example</h3>
<p>One training example: input x = 2, true answer y = 10. The model is just prediction = w * x, and we start with w = 3. For this loss, gradient = 2 x (prediction - y) x x. Use a learning rate of 0.05.</p>
<pre><code>Step 1: prediction = 3*2 = 6      error = -4   loss = 16
        gradient = 2 * (-4) * 2 = -16
        w = 3 - 0.05*(-16) = 3.8
Step 2: prediction = 7.6          error = -2.4 loss = 5.76
        gradient = 2 * (-2.4) * 2 = -9.6
        w = 3.8 + 0.48 = 4.28
Step 3: prediction = 8.56         loss = 2.07</code></pre>
<p>The loss falls each step (16, 5.76, 2.07) and w moves toward 5, the perfect answer. The steps get smaller automatically because the slope gets flatter near the bottom.</p>

<blockquote><p><strong>Quick check:</strong> In the example, why did the weight increase instead of decrease?</p><p>Answer: The gradient was negative, so subtracting it increased w, which is the direction that lowers the loss.</p></blockquote>

<h3>Choosing the learning rate</h3>
<ul>
<li><strong>Too small:</strong> learning is painfully slow.</li>
<li><strong>Too large:</strong> the steps overshoot the valley and the loss can bounce around or explode.</li>
<li><strong>About right:</strong> the loss falls smoothly. Many projects start with a library default such as the Adam optimiser and adjust only if the loss curve looks wrong.</li>
</ul>
<p>With a learning rate of 0.3 in our example, the first step gives w = 3 + 4.8 = 7.8, which overshoots the ideal 5. That is the danger of a big step.</p>

<blockquote><p><strong>Quick check:</strong> The training loss keeps jumping up and down wildly. What is a sensible first change?</p><p>Answer: Lower the learning rate, because steps that are too big overshoot the minimum.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Copy the three-step example into a table in a notebook or on paper.</li>
<li>Repeat it with learning rate 0.1. What happens at step 1?</li>
<li>Write a Python loop: <code>w = 3.0</code>, then repeat 10 times: <code>grad = 2*(w*2-10)*2</code> and <code>w = w - 0.05*grad</code>. Print w each time.</li>
<li>Watch w approach 5. Try a learning rate of 0.3 and describe what you see.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Loss measures how wrong the model is; training tries to reduce it.</li>
<li>Gradient descent moves each weight opposite to the gradient, scaled by the learning rate.</li>
<li>Too small a learning rate is slow; too large overshoots.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Backpropagation, batches and the training loop',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how a network learns across many examples, and to tell training from inference. This matters because knowing the words epoch, batch and step helps you read training logs and plan how long a job will take.</p>

<h3>How the gradient reaches every weight</h3>
<p>A real network has many layers, so how do we know how to change a weight deep inside? <strong>Backpropagation</strong> answers this. It works backward from the loss, using the chain rule from school mathematics: how much the loss changes with the output, times how much the output changes with the hidden value, and so on. You do not calculate it by hand in real life, because libraries do it automatically, but one small example shows the idea.</p>
<pre><code>x = 2, w1 = 1.5, w2 = 0.5, target t = 2
h = w1 * x = 3          y = w2 * h = 1.5        loss = (y - t)^2 = 0.25
dLoss/dw2 = 2*(y - t) * h      = 2*(-0.5)*3     = -3
dLoss/dw1 = 2*(y - t) * w2 * x = 2*(-0.5)*0.5*2 = -1</code></pre>
<p>Both gradients are negative, so both weights should increase a little. The error flows backward through w2 to reach w1.</p>

<h3>Training versus inference</h3>
<table>
<thead><tr><th></th><th>Training</th><th>Inference</th></tr></thead>
<tbody>
<tr><td>Purpose</td><td>Learn the weights</td><td>Use the learned weights to predict</td></tr>
<tr><td>Steps</td><td>Forward pass, loss, backward pass, update</td><td>Forward pass only</td></tr>
<tr><td>Cost</td><td>Heavy, repeated many times</td><td>Light per prediction</td></tr>
<tr><td>Where</td><td>Notebook, workstation or cloud GPU</td><td>Phone, website, small server</td></tr>
</tbody>
</table>

<h3>Epochs, batches and steps</h3>
<ul>
<li>An <strong>epoch</strong> is one full pass through all the training data.</li>
<li>A <strong>batch</strong> is a small group of examples used for one weight update. Batches make training faster and smoother than using one example at a time or all examples at once.</li>
<li>A <strong>step</strong> is one weight update.</li>
</ul>
<p>With 1,000 training examples and a batch size of 50, one epoch has 1,000 / 50 = 20 steps. Training for 10 epochs means 200 updates.</p>

<blockquote><p><strong>Quick check:</strong> You have 3,200 images and a batch size of 32. How many steps are in one epoch?</p><p>Answer: 100, because 3,200 divided by 32 is 100.</p></blockquote>

<h3>The loop in words</h3>
<ol>
<li>Start with small random weights.</li>
<li>Take a batch and run the forward pass.</li>
<li>Calculate the loss.</li>
<li>Run backpropagation to get the gradients.</li>
<li>Update the weights with the optimiser (such as SGD or Adam).</li>
<li>Repeat for every batch, for many epochs.</li>
</ol>
<p>Random starting weights matter: if every weight began at the same value, all neurons in a layer would learn the same thing.</p>

<h3>Scenario</h3>
<p>A team trains an image model and sees "epoch 3/20, 45 seconds per epoch". They can estimate the total time: 20 x 45 seconds = 15 minutes. If they double the data, each epoch takes about twice as long, so the total becomes about 30 minutes. Estimating this early stops a free notebook session from being wasted.</p>

<blockquote><p><strong>Quick check:</strong> Does inference run backpropagation?</p><p>Answer: No, inference only runs the forward pass because the weights are already learned and are not changed.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Calculate the steps per epoch for 2,500 examples with a batch size of 100.</li>
<li>Work out how many updates 8 epochs would give.</li>
<li>Redo the backpropagation example with w2 = 1 instead of 0.5 and see how the gradients change.</li>
<li>Write two sentences explaining training and inference to someone who runs a small business.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Backpropagation sends the error backward to give every weight its gradient.</li>
<li>One epoch is a full pass over the data; one step is one batch update.</li>
<li>Training changes weights; inference only uses them.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: A neuron has inputs 4 and 1, weights 0.5 and 2, and bias -1. What is its output before any activation?
- 5
* 3
- 4
- 7
= (4 x 0.5) + (1 x 2) - 1 = 2 + 2 - 1 = 3.

Q: A dense layer has 6 inputs and 4 neurons. How many parameters does it have?
- 24
- 10
* 28
- 30
= There are 6 x 4 = 24 weights plus 4 biases, giving 28.

Q: Why do hidden layers need an activation function such as ReLU?
- To make the network run faster on a phone
* To bend the numbers so the network can learn curved, complex patterns
- To remove the bias terms
- To make all outputs equal
= Without activations, stacked layers collapse into one straight-line formula.

Q: A network must decide which one of four crop diseases is shown in a photo. Which output activation fits best?
- ReLU
- No activation, since four numbers are enough
- Sigmoid on a single neuron
* Softmax over four neurons
= Softmax turns four scores into probabilities that add up to 1, which suits choosing one class.

Q: With learning rate 0.1, weight w = 2 and gradient -5, what is the new weight?
- 1.5
- 2.05
* 2.5
- 7
= New weight = old weight - learning rate x gradient = 2 - 0.1 x (-5) = 2 + 0.5 = 2.5.

Q: Training loss goes up and down wildly and sometimes becomes huge. What is the best first thing to try?
- Train for more epochs
* Lower the learning rate
- Add more hidden layers
- Remove the validation data
= Oversized steps overshoot the minimum, so a smaller learning rate usually stabilises training.

Q: There are 2,000 training examples and the batch size is 50. How many weight updates happen in one epoch?
- 50
- 2,000
* 40
- 100
= 2,000 divided by 50 is 40 steps per epoch.

Q: Which statement about inference is correct?
* It runs only the forward pass using already-learned weights
- It repeats backpropagation for every prediction
- It needs the training labels
- It changes the weights slightly each time
= Inference uses the trained model to predict; weights stay fixed.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 2
        [
            'title'   => 'Module 2: Training well: data, overfitting and evaluation',
            'summary' => 'Prepare data properly, spot overfitting, regularise, and judge a model with the right measures.',
            'lessons' => [
                [
                    'title'   => 'Overfitting and the validation set',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to split data correctly and recognise overfitting from training curves. This matters because a model that looks perfect on the data it has seen, but fails on new data, is worse than useless: it gives false confidence.</p>

<h3>Memorising versus learning</h3>
<p>Think of two students preparing for an exam. One memorises last year's questions and answers exactly. The other understands the topic. On last year's paper the first scores 100 percent; on a new paper the first fails. A neural network with many parameters can behave like the first student. This is <strong>overfitting</strong>: the model fits the noise and quirks of the training data instead of the general pattern. The opposite problem, <strong>underfitting</strong>, is when the model is too simple to learn even the training data.</p>

<h3>Three data sets</h3>
<table>
<thead><tr><th>Set</th><th>Typical share</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td>Training</td><td>70 percent</td><td>Used to learn the weights</td></tr>
<tr><td>Validation</td><td>15 percent</td><td>Used while building, to compare settings and spot overfitting</td></tr>
<tr><td>Test</td><td>15 percent</td><td>Used once at the end for an honest final score</td></tr>
</tbody>
</table>
<p>The test set is like the real exam: if you keep peeking at it while improving the model, it stops being an honest measure.</p>

<h3>Reading the curves</h3>
<p>Plot the loss (or accuracy) on training and validation data after every epoch.</p>
<table>
<thead><tr><th>What you see</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>Both losses fall together</td><td>Healthy learning</td></tr>
<tr><td>Training loss falls, validation loss rises</td><td>Overfitting began at the turning point</td></tr>
<tr><td>Both losses stay high</td><td>Underfitting: bigger model, better features or more training</td></tr>
</tbody>
</table>
<p>Example: a model reaches 99 percent training accuracy but only 72 percent validation accuracy. The 27-point gap is a clear overfitting warning.</p>

<blockquote><p><strong>Quick check:</strong> Training accuracy is 97 percent and validation accuracy is 70 percent. What is the likely problem?</p><p>Answer: Overfitting, because the model does far better on data it has seen than on new data.</p></blockquote>

<h3>Data leakage</h3>
<p><strong>Leakage</strong> happens when information from the validation or test data sneaks into training, making scores look better than they really are. Common causes: the same person's photos appearing in both training and test sets, scaling the numbers using statistics from the whole data set before splitting, or duplicate rows copied across sets. Always split first, then process, and keep related items (the same customer, the same patient) in one set only.</p>

<h3>Scenario</h3>
<p>A student collects 20 photos from each of 50 market stalls and randomly splits the 1,000 photos. The test accuracy is 98 percent, but at a new market it drops to 70 percent. The model had learned stall backgrounds, because photos of the same stall appeared in both training and test sets. Splitting by stall, not by photo, would have revealed the true performance.</p>

<blockquote><p><strong>Quick check:</strong> Why should you keep all photos of one patient in the same data set?</p><p>Answer: If they were spread across sets the model could recognise the patient rather than the condition, which is leakage and inflates the score.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>List 100 imaginary items numbered 1 to 100 and decide which numbers go to training, validation and test using a 70/15/15 split.</li>
<li>Write what you would do if the 100 items came from only 10 people.</li>
<li>Look at any training log you have seen (or the Keras example later in this course) and note whether validation loss ever rises.</li>
<li>Explain overfitting to a friend using the exam example in your own words.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Overfitting means great training scores and poor scores on new data.</li>
<li>Use separate training, validation and test sets; touch the test set once.</li>
<li>Split by group (person, place) when items are related, to avoid leakage.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Regularisation and tuning',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to choose practical methods to reduce overfitting and tune a model sensibly. This matters because most real projects do not fail from lack of clever models but from a model that does not generalise.</p>

<h3>What regularisation means</h3>
<p><strong>Regularisation</strong> is any method that makes it harder for a model to memorise, so it must learn general patterns. Here are the main tools, from the cheapest to the most work.</p>
<table>
<thead><tr><th>Method</th><th>How it helps</th></tr></thead>
<tbody>
<tr><td>More (and more varied) data</td><td>Harder to memorise; usually the best fix</td></tr>
<tr><td>Data augmentation</td><td>Creates new training examples by flipping, rotating or slightly changing images</td></tr>
<tr><td>Dropout</td><td>Randomly switches off some neurons during training so none can be relied on too much</td></tr>
<tr><td>Weight penalty (L2)</td><td>Adds a cost for very large weights, keeping the model smoother</td></tr>
<tr><td>Early stopping</td><td>Stops training when validation loss stops improving</td></tr>
<tr><td>Smaller model</td><td>Fewer parameters means less capacity to memorise</td></tr>
</tbody>
</table>

<h3>Dropout and early stopping in Keras</h3>
<pre><code>from keras import layers, callbacks

layers.Dropout(0.3)    # switch off 30% of the neurons while training

stop = callbacks.EarlyStopping(monitor="val_loss", patience=3,
                               restore_best_weights=True)
# model.fit(..., callbacks=[stop])</code></pre>
<p>Dropout is active only during training; at inference all neurons work. With <code>patience=3</code>, training stops after three epochs without improvement and the best weights are restored.</p>

<blockquote><p><strong>Quick check:</strong> Is dropout used while predicting on new images?</p><p>Answer: No, it is only applied during training; at prediction time all neurons are used.</p></blockquote>

<h3>Hyperparameters</h3>
<p>Weights are learned by training. <strong>Hyperparameters</strong> are the settings you choose before training: learning rate, batch size, number of layers and neurons, dropout rate and epochs. Tune them using the validation set, never the test set. A simple approach is to change one setting at a time and record the result.</p>
<table>
<thead><tr><th>Run</th><th>Change</th><th>Validation accuracy</th></tr></thead>
<tbody>
<tr><td>1</td><td>Baseline, no dropout</td><td>78%</td></tr>
<tr><td>2</td><td>Dropout 0.3</td><td>83%</td></tr>
<tr><td>3</td><td>Dropout 0.3 plus augmentation</td><td>87%</td></tr>
</tbody>
</table>
<p>Keeping a log like this teaches you what actually helps, and it gives you evidence to show a client or teacher.</p>

<h3>Start simple</h3>
<p>Always build a small baseline first. A tiny model that gets 80 percent tells you the task is possible and gives you a number to beat. Jumping straight to a huge model hides problems in the data.</p>

<h3>Scenario</h3>
<p>A trainee builds a model that sorts photos of cassava leaves into healthy and diseased. With 300 photos the training accuracy reaches 100 percent but validation stays at 76 percent. She adds random flips and small rotations, adds dropout of 0.3 and uses early stopping. Validation rises to 86 percent while training falls to 91 percent. The smaller gap shows the model now generalises better.</p>

<blockquote><p><strong>Quick check:</strong> You tuned the learning rate by watching the test set score. What is wrong?</p><p>Answer: The test set is no longer independent because it influenced your choices, so use the validation set for tuning.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Create a table like the one above in a spreadsheet with columns Run, Change and Validation accuracy.</li>
<li>Decide the first three changes you would test on a model that overfits and write them in order of effort.</li>
<li>Look up the Keras documentation page for <code>Dropout</code> and note what the rate means.</li>
<li>Write one sentence on why you would build a simple baseline first.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>More varied data, augmentation, dropout, weight penalties, early stopping and smaller models fight overfitting.</li>
<li>Hyperparameters are your settings; tune them on validation data only.</li>
<li>Keep an experiment log and start with a simple baseline.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Preparing and labelling data',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to plan a data set for a deep learning project: collect it, label it, clean it and prepare it for a model. This matters because data quality usually decides the result more than the choice of model, and "garbage in, garbage out" is very true for neural networks.</p>

<h3>Start with the question</h3>
<p>Before collecting anything, write down what the model must decide, what the input will look like when it is used for real, and what mistakes matter most. If the model will be used with phone photos taken outdoors by farmers, then training photos from a clean studio will mislead you. Training data should look like real-life data.</p>

<h3>Collecting data responsibly</h3>
<ul>
<li>Get permission. Photos of people, children or private records need consent and care under data protection rules such as Uganda's Data Protection and Privacy Act.</li>
<li>Collect varied examples: different light, phones, backgrounds, ages, accents and handwriting.</li>
<li>Do not scrape material you have no right to use.</li>
<li>Record where each item came from and the date.</li>
</ul>

<h3>Labelling</h3>
<p>A <strong>label</strong> is the correct answer attached to an example, such as "healthy" or "spam". For supervised learning, label quality is critical.</p>
<ol>
<li>Write clear label rules with examples and edge cases ("a leaf with one small spot counts as healthy").</li>
<li>Have two people label a sample and measure how often they agree. Low agreement means the rules are unclear.</li>
<li>Ask an expert (an extension officer, a clinician) to check difficult cases.</li>
<li>Keep the final label list in a spreadsheet or in folders named by class.</li>
</ol>

<h3>Cleaning and balancing</h3>
<table>
<thead><tr><th>Problem</th><th>What to do</th></tr></thead>
<tbody>
<tr><td>Duplicates</td><td>Remove them, especially across training and test sets</td></tr>
<tr><td>Wrong or doubtful labels</td><td>Review and correct or remove</td></tr>
<tr><td>Missing values</td><td>Fill in sensibly or drop the row, and record your choice</td></tr>
<tr><td>Very uneven classes (for example 95 percent healthy)</td><td>Collect more of the rare class, use class weights, or judge with recall and precision instead of accuracy</td></tr>
<tr><td>Different image sizes</td><td>Resize all to the size the model expects</td></tr>
</tbody>
</table>

<h3>Preparing inputs</h3>
<p>Neural networks learn best when numbers are on similar scales. Images have pixel values 0 to 255, so divide by 255 to get 0 to 1. Numeric columns are often <strong>standardised</strong>: subtract the average and divide by the spread. Calculate these statistics from the training set only, then apply them to validation and test data, to avoid leakage. Text is split into tokens and converted to numbers, a topic we meet in Module 3.</p>

<blockquote><p><strong>Quick check:</strong> A data set has 950 healthy and 50 diseased leaf photos. Why is 95 percent accuracy not impressive?</p><p>Answer: A model that always says "healthy" scores 95 percent without detecting any disease, so you also need recall for the rare class.</p></blockquote>

<h3>Scenario</h3>
<p>A clinic wants a model to flag poor-quality chest X-ray images before they are read. Two nurses label 200 images and agree on only 70 percent. Instead of training, the team rewrites the rules with sample pictures for "blurry", "cut off" and "good" and re-labels. Agreement rises to 92 percent. Only now is the data worth training on.</p>

<blockquote><p><strong>Quick check:</strong> From which data should you calculate the average used to standardise a column?</p><p>Answer: The training set only, because using validation or test data would leak information into training.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Pick a small classification idea from your area, such as sorting mobile money messages into "payment" and "scam".</li>
<li>Write a one-page label guide with at least two examples per class and two tricky cases.</li>
<li>Collect or write 20 examples and ask a friend to label them using only your guide.</li>
<li>Compute the agreement as matching labels divided by 20 and improve the guide if it is below 90 percent.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Training data must look like the data the model will meet in real use.</li>
<li>Clear label rules and agreement checks improve label quality.</li>
<li>Remove duplicates, watch class balance and scale inputs using training-set statistics only.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Evaluating models: confusion matrix, precision and recall',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build a confusion matrix and calculate accuracy, precision, recall and F1 score. This matters because a single accuracy number can hide serious failures, and different projects need different measures.</p>

<h3>The confusion matrix</h3>
<p>For a yes/no problem, each prediction falls into one of four boxes. Imagine a model that flags patients who may have a disease ("positive").</p>
<table>
<thead><tr><th></th><th>Predicted positive</th><th>Predicted negative</th></tr></thead>
<tbody>
<tr><td>Actually positive</td><td>True positive (TP)</td><td>False negative (FN), a missed case</td></tr>
<tr><td>Actually negative</td><td>False positive (FP), a false alarm</td><td>True negative (TN)</td></tr>
</tbody>
</table>

<h3>The four measures</h3>
<pre><code>accuracy  = (TP + TN) / all
precision = TP / (TP + FP)    "when it says yes, how often is it right?"
recall    = TP / (TP + FN)    "of all real yes cases, how many did it find?"
F1        = 2 * precision * recall / (precision + recall)</code></pre>

<h3>A worked example</h3>
<p>The model screens 100 patients. Results: TP = 8, FN = 2, FP = 5, TN = 85.</p>
<pre><code>accuracy  = (8 + 85) / 100 = 93%
precision = 8 / (8 + 5)  = 8/13 = 61.5%
recall    = 8 / (8 + 2)  = 80%
F1        = 2 * 0.615 * 0.80 / (0.615 + 0.80) = about 0.70</code></pre>
<p>93 percent accuracy sounds excellent, yet a lazy model that says "healthy" for everyone would score 90 percent accuracy with 0 percent recall. Only 10 of the 100 patients have the disease, so accuracy hides the problem.</p>

<blockquote><p><strong>Quick check:</strong> Out of 20 spam messages a filter catches 15 and lets 5 through. What is the recall for spam?</p><p>Answer: 75 percent, because 15 of the 20 real spam messages were found (15 / (15 + 5)).</p></blockquote>

<h3>Which measure matters?</h3>
<table>
<thead><tr><th>Situation</th><th>Costly mistake</th><th>Focus on</th></tr></thead>
<tbody>
<tr><td>Screening for a serious disease</td><td>Missing a sick person</td><td>High recall</td></tr>
<tr><td>Blocking a customer's mobile money account as fraud</td><td>Wrongly blocking honest users</td><td>High precision</td></tr>
<tr><td>Balanced classes, equal costs</td><td>Either</td><td>Accuracy or F1</td></tr>
</tbody>
</table>
<p>Many classifiers output a probability. Moving the <strong>threshold</strong> (for example flag when above 0.3 instead of 0.5) raises recall but lowers precision, and the reverse. Choose it by the cost of each mistake, using the validation set.</p>

<h3>For more than two classes</h3>
<p>The confusion matrix becomes a larger grid, with true classes down the side and predicted classes across the top. The diagonal shows correct answers. Large numbers off the diagonal show which classes the model mixes up, such as two similar crop diseases, so you know what data to add.</p>

<blockquote><p><strong>Quick check:</strong> A fraud model flags 50 transactions and only 10 are truly fraud. Is precision high or low?</p><p>Answer: Low, at 20 percent (10 of 50), because most of its alarms are false.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Use the numbers TP = 30, FN = 10, FP = 20, TN = 140 and calculate accuracy, precision, recall and F1.</li>
<li>Invent a case where a model with 99 percent accuracy is useless and explain why.</li>
<li>Decide whether precision or recall matters more for a model that spots leaking pipes from photos, and justify it.</li>
<li>In a notebook, if you know scikit-learn, try <code>from sklearn.metrics import classification_report</code> on a few made-up lists.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>The confusion matrix counts TP, FP, FN and TN.</li>
<li>Precision asks how trustworthy the alarms are; recall asks how many real cases were found.</li>
<li>Accuracy can mislead on uneven classes; match the measure to the cost of mistakes.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Training accuracy is 99 percent and validation accuracy is 74 percent. What is the most likely issue?
- Underfitting
* Overfitting
- The learning rate is exactly right
- The test set is too large
= A large gap between training and validation performance is the classic sign of overfitting.

Q: When should you use the test set?
- After every epoch to guide training
- To choose the learning rate
* Once, at the end, for an honest final score
- Never; it is optional
= Repeated use of the test set turns it into a tuning set and makes the final score over-optimistic.

Q: A data set has several photos of each of 40 patients. Which split avoids leakage?
- Random split of all photos
* Keep all photos of one patient in the same set
- Put the clearest photos in the test set
- Copy every photo into all three sets
= If one patient appears in both training and test sets, the model can recognise the person instead of the condition.

Q: Which of these help reduce overfitting? (choose all that apply)
* Data augmentation
* Dropout
* Early stopping
- Training for many more epochs without checking validation loss
= Augmentation, dropout and early stopping all fight memorisation. More blind training usually makes overfitting worse.

Q: Two nurses label the same 100 images and agree on only 65. What should you do before training?
- Train anyway; the network will average out the mistakes
- Delete half the images
* Clarify the label rules with examples and re-label
- Add more hidden layers
= Low agreement means the labels are unreliable, and a model cannot learn a clear pattern from inconsistent labels.

Q: Where should the mean used to standardise a column be calculated?
- From the test set
- From the whole data set before splitting
* From the training set only
- From the validation set
= Using the training set only prevents information from validation and test data from leaking into training.

Q: A model screens 100 people. TP = 6, FN = 4, FP = 10, TN = 80. What is the recall?
- 37.5 percent
- 86 percent
* 60 percent
- 90 percent
= Recall = TP / (TP + FN) = 6 / 10 = 60 percent.

Q: A bank wants to avoid wrongly freezing honest customers' accounts. Which measure should it watch most closely?
* Precision
- Recall
- Number of layers
- Training time
= Precision shows how often a flagged account is truly fraud; low precision means many honest customers are frozen.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 3
        [
            'title'   => 'Module 3: Architectures: images, sequences and transformers',
            'summary' => 'See how convolutional networks read images and how attention and transformers handle language.',
            'lessons' => [
                [
                    'title'   => 'Convolutional neural networks for images',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain how a convolutional neural network (CNN) reads an image and calculate a simple convolution by hand. This matters because CNNs are behind photo search, face unlock, crop-disease apps and medical image tools.</p>

<h3>Why not just use dense layers?</h3>
<p>A small 28 x 28 grey image has 784 pixels. A dense layer with 100 neurons would need 784 x 100 = 78,400 weights, and each weight would be tied to one exact pixel position. A cat in the corner would look completely different from a cat in the middle. Colour photos are far bigger. We need a method that reuses the same small detector everywhere in the image.</p>

<h3>The convolution idea</h3>
<p>A <strong>filter</strong> (or kernel) is a small grid of weights, such as 3 x 3. It slides over the image. At each position it multiplies the filter weights by the pixel values under it and adds them up, giving one number. The grid of results is a <strong>feature map</strong>: it shows where that pattern (an edge, a corner, a texture) appears.</p>
<pre><code>patch:   1 0 2     filter:  1 0 0
         0 1 0              0 1 0
         3 0 1              0 0 1

result = (1*1) + (1*1) + (1*1) = 3     (the other products are 0)</code></pre>
<p>This diagonal filter gives a high number where the patch has a diagonal line. The filter weights are learned during training, not designed by hand.</p>

<blockquote><p><strong>Quick check:</strong> Why does a CNN find a cat whether it is in the corner or the middle?</p><p>Answer: The same filter slides over every position, so a pattern is detected wherever it appears.</p></blockquote>

<h3>Building blocks</h3>
<table>
<thead><tr><th>Layer</th><th>What it does</th></tr></thead>
<tbody>
<tr><td>Convolution</td><td>Applies many learned filters to produce feature maps</td></tr>
<tr><td>ReLU</td><td>Activation applied to the feature maps</td></tr>
<tr><td>Pooling</td><td>Shrinks the maps, for example keeping the largest value in each 2 x 2 block</td></tr>
<tr><td>Flatten and dense</td><td>Combine the final features into class scores</td></tr>
</tbody>
</table>
<p>Max pooling of the block [1, 3 / 4, 2] gives 4. The output size of a convolution without padding is: (image size - filter size) / stride + 1. For a 28-pixel image and a 3 x 3 filter with stride 1, it is (28 - 3) / 1 + 1 = 26.</p>

<h3>How much smaller is it?</h3>
<p>A convolution layer with 32 filters of size 3 x 3 on a grey image has 32 x (9 + 1) = 320 parameters, compared with 78,400 for the dense layer. Fewer parameters means less overfitting and faster training.</p>

<h3>What the layers learn</h3>
<p>Early layers find edges and simple colours. Middle layers combine them into shapes such as eyes, wheels or leaf veins. Deep layers recognise whole objects. This build-up from simple to complex is why depth helps.</p>

<h3>Scenario</h3>
<p>A farmers' cooperative wants a phone app that tells whether a bean leaf shows rust disease. A small CNN with three convolution blocks and pooling is trained on 2,000 labelled leaf photos with random flips as augmentation. The same filters that detect rust-coloured spots work whether the spots are near the leaf tip or the stem.</p>

<blockquote><p><strong>Quick check:</strong> What is the output size of a 3 x 3 filter, stride 1, on a 10 x 10 image with no padding?</p><p>Answer: 8 x 8, because (10 - 3) / 1 + 1 = 8.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>On paper, draw a 4 x 4 grid of numbers and a 2 x 2 filter.</li>
<li>Calculate the convolution result for the top-left position and the one just to its right.</li>
<li>Work out the size of the output map and then the size after 2 x 2 max pooling.</li>
<li>Count the parameters of a layer with 16 filters of size 3 x 3 on a grey image.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>A filter slides over the image, so the same pattern is found anywhere.</li>
<li>Convolution plus pooling shrinks the image while building richer features.</li>
<li>CNNs use far fewer parameters than dense layers on images.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Sequences, tokens and attention',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to describe how a model handles text, explain tokens and embeddings, and calculate a simple attention weight. This matters because every modern language tool is built on these ideas, and knowing them helps you understand both the strengths and the limits of AI assistants.</p>

<h3>Why order matters</h3>
<p>"The dog bit the man" and "The man bit the dog" use the same words but mean different things. Data such as sentences, speech, sensor readings and mobile money histories are <strong>sequences</strong>, where order carries meaning. Older sequence models, called recurrent neural networks (RNNs) and their improved forms such as LSTMs, read one item at a time and carried a memory forward. They struggled with long texts because early information faded and the one-by-one reading was slow.</p>

<h3>Tokens and embeddings</h3>
<p>A network only works with numbers, so text is converted in two steps.</p>
<ol>
<li><strong>Tokenisation.</strong> The text is cut into <strong>tokens</strong>, which are pieces of words. A common word may be one token; a rare word is split into several. Each token has an ID number in a fixed vocabulary.</li>
<li><strong>Embedding.</strong> Each token ID is turned into a list of numbers called an <strong>embedding</strong>. These lists are learned so that words used in similar ways end up with similar numbers.</li>
</ol>
<pre><code>"Matooke is cheap"  ->  tokens: ["Mat", "ooke", " is", " cheap"]
                    ->  IDs:    [ 5521, 8810, 318, 7102 ]   (example numbers)
                    ->  each ID becomes a vector such as [0.12, -0.7, 0.33, ...]</code></pre>
<p>The tokens above are an illustration; real tokenisers differ. A side effect is that languages with less data may be split into more tokens, which can make them slower and more costly to process.</p>

<blockquote><p><strong>Quick check:</strong> What is an embedding?</p><p>Answer: A learned list of numbers that represents a token so that similar meanings get similar numbers.</p></blockquote>

<h3>Attention: looking at what matters</h3>
<p>Take the sentence "The bank approved her loan because it was safe." What does "it" refer to? To decide, the model should look more at some words than others. <strong>Attention</strong> lets each token compute a score for every other token, convert the scores into weights that add to 1, and then take a weighted mix of the information.</p>
<pre><code>scores for one token:  2, 1, 0
softmax -> e^2 = 7.39, e^1 = 2.72, e^0 = 1.00   total 11.11
weights = 0.665, 0.245, 0.090</code></pre>
<p>This token pays about 66 percent of its attention to the first word, 25 percent to the second and 9 percent to the third. Because every token can look directly at every other token, long-range links are easy, and all tokens can be processed at the same time, which suits fast graphics processors.</p>

<blockquote><p><strong>Quick check:</strong> Attention weights for one token are 0.5, 0.3 and 0.2. What do they add up to, and why?</p><p>Answer: They add to 1, because softmax turns the scores into shares of attention.</p></blockquote>

<h3>Scenario</h3>
<p>A company builds a tool to classify customer messages in English and Luganda. It notices that messages in Luganda produce nearly twice as many tokens, so they cost more to process and often reach the length limit sooner. The team shortens long messages and tests quality separately for each language rather than assuming one score fits both.</p>

<h3>Try it yourself</h3>
<ol>
<li>Write a short sentence in your own language and in English.</li>
<li>Guess how many tokens each would use, then check with the free tokeniser tool offered by an AI provider if you have one, or just compare the number of word pieces.</li>
<li>Compute softmax weights for scores 3, 1 and 1 using a calculator.</li>
<li>Explain in two sentences why word order matters for a model.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Text becomes tokens, then embeddings, before a network can use it.</li>
<li>Attention gives each token a weighted look at all the others.</li>
<li>Attention handles long-range links and can run in parallel, unlike older step-by-step RNNs.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Transformers and how large language models are built',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain at a high level how a transformer works and how a large language model (LLM) goes from raw text to a helpful assistant. This matters because you will use these systems at work, and understanding how they are built helps you judge their claims and risks.</p>

<h3>The transformer block</h3>
<p>A <strong>transformer</strong> is a network made of repeated blocks. Each block has two main parts:</p>
<ol>
<li><strong>Self-attention</strong>, in which every token looks at the other tokens and gathers useful information. Several attention "heads" work side by side, each able to focus on a different kind of relationship.</li>
<li><strong>A small dense network</strong> applied to each token to process what it gathered.</li>
</ol>
<p>Because attention ignores order by itself, the model also adds <strong>position information</strong> to the embeddings so it knows which token comes first. Blocks are stacked many times; large models use dozens. Around each part, shortcuts called residual connections and a normalising step keep deep training stable.</p>

<h3>Predicting the next token</h3>
<p>A typical chat model is a <strong>decoder</strong> transformer. It reads the tokens so far and outputs a probability for every token in its vocabulary as the next one. One token is chosen, added to the text, and the process repeats. A setting often called temperature controls randomness: low values make the choice more predictable, higher values more varied.</p>

<h3>The stages of building an LLM</h3>
<table>
<thead><tr><th>Stage</th><th>What happens</th><th>Cost</th></tr></thead>
<tbody>
<tr><td>Pretraining</td><td>The model learns to predict the next token on a huge collection of text</td><td>Very high: many accelerators for weeks or months</td></tr>
<tr><td>Instruction tuning</td><td>It is trained on examples of questions with good answers so it follows instructions</td><td>Much lower</td></tr>
<tr><td>Preference tuning</td><td>People or other models rate answers and the model is trained to prefer the better ones and to avoid harmful replies</td><td>Lower</td></tr>
<tr><td>Deployment</td><td>The model answers users, sometimes with search or tools added</td><td>Ongoing</td></tr>
</tbody>
</table>

<blockquote><p><strong>Quick check:</strong> Which stage teaches a model the general patterns of language from huge amounts of text?</p><p>Answer: Pretraining, where it learns by predicting the next token over and over.</p></blockquote>

<h3>Size and memory</h3>
<p>The number of parameters tells you the size. Storing a model needs memory: a 7-billion-parameter model with each weight stored in 16 bits (2 bytes) needs about 7 billion x 2 = 14 billion bytes, which is roughly 14 GB, just to load. Compressing the weights to fewer bits (<strong>quantisation</strong>) can shrink this, with a small drop in quality, which is how some models run on laptops.</p>
<p>The <strong>context window</strong> is the maximum number of tokens the model can consider at once, covering your question and its answer. Longer contexts need more memory and time.</p>

<h3>Why LLMs still make mistakes</h3>
<ul>
<li>They predict likely text, not verified truth, so they can hallucinate.</li>
<li>Their knowledge stops at the training cut-off unless a search tool is added.</li>
<li>Training data may carry bias, and some languages and regions have far less data.</li>
</ul>

<h3>Scenario</h3>
<p>A university IT team wants an assistant to answer student questions about timetables. Training a new LLM is out of the question. Instead they use an existing model and add a retrieval step: the system searches the official timetable documents, pastes the relevant passages into the prompt and tells the model to answer only from them. This reduces invented answers without any new training of the model.</p>

<blockquote><p><strong>Quick check:</strong> Roughly how much memory is needed to load a 3-billion-parameter model at 16 bits per weight?</p><p>Answer: About 6 GB, because 3 billion weights times 2 bytes is 6 billion bytes.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose any AI chat tool and ask it to continue the sentence "The boda boda driver stopped because..." three times; notice the variety.</li>
<li>Calculate the memory for a 13-billion-parameter model at 16 bits and at 8 bits per weight.</li>
<li>Write the four stages of building an assistant in your own words.</li>
<li>List two limits of an LLM that you would tell a new user about.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Transformers stack self-attention and dense layers, with position information added.</li>
<li>LLMs predict the next token; assistants are made with pretraining, then instruction and preference tuning.</li>
<li>Memory needs follow parameters times bytes per weight; retrieval can ground answers.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why are convolutional layers better than dense layers for image recognition?
- They use more parameters, which always improves accuracy
* The same small filter is reused at every position, which needs fewer parameters and finds patterns anywhere
- They do not need training
- They only work on black and white images
= Weight sharing across positions makes CNNs efficient and position-tolerant.

Q: A 3 x 3 filter with stride 1 and no padding is applied to a 12 x 12 image. What is the size of the output map?
- 12 x 12
- 9 x 9
* 10 x 10
- 11 x 11
= (12 - 3) / 1 + 1 = 10.

Q: What does a 2 x 2 max pooling layer do?
- It doubles the image size
* It keeps the largest value in each 2 x 2 block, shrinking the map
- It adds random noise to the image
- It changes the class labels
= Pooling reduces size and keeps the strongest signals.

Q: What is the role of an embedding in a language model?
- It stores the user's password
* It turns a token into a learned list of numbers that captures meaning
- It spell-checks the text
- It deletes rare words
= Embeddings give each token a numeric representation in which similar words have similar numbers.

Q: The attention scores for one token are 2, 1 and 0, giving weights 0.665, 0.245 and 0.090. What do these weights show?
- The probability of the answer being correct
* How much that token focuses on each of the other three tokens
- The number of letters in each word
- The order in which the tokens were typed
= Attention weights add to 1 and describe how much each token is used in the weighted mix.

Q: In which stage does an LLM learn general language patterns from a very large text collection?
- Preference tuning
- Deployment
* Pretraining
- Evaluation
= Pretraining by next-token prediction is the largest and most expensive stage.

Q: Why is a 7-billion-parameter model stored at 16 bits per weight about 14 GB?
- Because 7 billion parameters take 14 bytes in total
- Because each weight needs 4 bytes
* Because 7 billion weights times 2 bytes each is about 14 billion bytes
- Because the context window adds 7 GB
= 16 bits is 2 bytes, so 7 billion x 2 bytes = 14 GB.

Q: A support chatbot keeps inventing answers about your company's return policy. Which fix is most practical without retraining the model?
- Make the temperature much higher
* Retrieve the policy text and instruct the model to answer only from it
- Ask users to type shorter questions
- Switch the model to produce shorter tokens
= Grounding answers in retrieved official text reduces hallucination.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 4
        [
            'title'   => 'Module 4: Building models in practice',
            'summary' => 'Run experiments in a free notebook, reuse pretrained models, and plan for compute, cost and energy.',
            'lessons' => [
                [
                    'title'   => 'Your first experiment in a Python notebook',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to open a free Google Colab notebook, train a small Keras model on handwritten digits and read the results. This matters because running your own experiments turns the ideas into skill, and it needs nothing more than a browser and a Google account.</p>

<h3>Setting up Colab</h3>
<p><strong>Google Colab</strong> is a free service that runs Python notebooks in the cloud. A notebook has <strong>cells</strong> of code that you run one at a time. Many libraries, including TensorFlow and Keras, are already installed. Free usage has limits that change over time, such as session length and available processors, so save your work and do not rely on a session lasting all day.</p>
<ol>
<li>Go to colab.research.google.com and sign in with your Google account.</li>
<li>Choose <strong>New notebook</strong>.</li>
<li>Optional: select <strong>Runtime</strong>, then <strong>Change runtime type</strong>, and pick a GPU if one is offered. This small example also runs on the normal processor.</li>
<li>Type code in a cell and press <strong>Shift + Enter</strong> to run it.</li>
</ol>
<p>Free alternatives include Kaggle Notebooks, or Jupyter on your own computer if it has enough memory.</p>

<h3>Train a digit classifier</h3>
<p>The MNIST data set holds 70,000 small images of handwritten digits 0 to 9. This code builds a small dense network.</p>
<pre><code>import keras
from keras import layers

(x_train, y_train), (x_test, y_test) = keras.datasets.mnist.load_data()
x_train = x_train / 255.0          # scale pixels to 0..1
x_test = x_test / 255.0

model = keras.Sequential([
    keras.Input(shape=(28, 28)),
    layers.Flatten(),
    layers.Dense(64, activation="relu"),
    layers.Dropout(0.2),
    layers.Dense(10, activation="softmax"),
])

model.compile(optimizer="adam",
              loss="sparse_categorical_crossentropy",
              metrics=["accuracy"])

history = model.fit(x_train, y_train, epochs=5, validation_split=0.1)
print(model.evaluate(x_test, y_test))</code></pre>
<p>You should see accuracy around 97 percent after a few epochs; exact numbers vary because the weights start randomly.</p>

<blockquote><p><strong>Quick check:</strong> Why does the code divide the pixel values by 255?</p><p>Answer: To scale values from 0-255 to 0-1, which helps the network train more smoothly.</p></blockquote>

<h3>Reading the output</h3>
<table>
<thead><tr><th>Item in the log</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td>loss, accuracy</td><td>Scores on the training batches</td></tr>
<tr><td>val_loss, val_accuracy</td><td>Scores on the 10 percent held out by <code>validation_split</code></td></tr>
<tr><td>evaluate result</td><td>Scores on the test set, to quote at the end</td></tr>
</tbody>
</table>
<p>If you prefer PyTorch, the same ideas apply: you define layers, a loss and an optimiser, then write a loop that runs the forward pass, the backward pass and the update. Keras hides that loop inside <code>fit</code>, which makes it a friendly place to start.</p>

<h3>Good notebook habits</h3>
<ul>
<li>Set a seed (for example <code>keras.utils.set_random_seed(42)</code>) so results are repeatable.</li>
<li>Write short notes in text cells on what you changed and why.</li>
<li>Download or save the notebook to Google Drive; free sessions can disconnect.</li>
<li>Never put passwords or private data in a shared notebook.</li>
</ul>

<h3>Scenario</h3>
<p>A learner changes the Dense layer from 64 to 8 neurons and training accuracy falls to 90 percent. Then she tries 256 neurons and gets 98 percent training but only 97.3 percent test accuracy, barely better than 64. She concludes that 64 or 128 is enough for this task, saving time and memory. This kind of small, recorded experiment is how practitioners decide on sizes.</p>

<blockquote><p><strong>Quick check:</strong> Which number should you quote as the final performance: validation accuracy during training or test accuracy at the end?</p><p>Answer: The test accuracy, because the test set was not used for any choices.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Open a new Colab notebook and paste the code above into one cell.</li>
<li>Run it and write down the final test accuracy.</li>
<li>Change the Dense layer to 16 neurons and run again; compare the accuracy.</li>
<li>Change <code>epochs=5</code> to <code>epochs=2</code> and note the effect.</li>
<li>Add a text cell summarising what you learned.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Colab and Kaggle give free notebooks; free limits can change.</li>
<li>Keras makes a small model short to write: Sequential, compile, fit, evaluate.</li>
<li>Change one thing at a time and keep notes of every result.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Transfer learning and fine-tuning',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to explain transfer learning and use a pretrained image model to build a classifier from only a few hundred photos. This matters because most people do not have millions of images or powerful computers, and reusing a trained model is the practical route to good results.</p>

<h3>Standing on someone else's training</h3>
<p>A model trained on a very large image collection has already learned edges, textures, shapes and parts of objects in its early and middle layers. Those skills help on almost any image task. <strong>Transfer learning</strong> keeps that knowledge and adds a new final part for your own classes. It needs less data, less time and less compute than training from scratch.</p>

<h3>Two ways to reuse a model</h3>
<table>
<thead><tr><th>Approach</th><th>What you do</th><th>When to use</th></tr></thead>
<tbody>
<tr><td>Feature extraction</td><td>Freeze the pretrained layers and train only a new final layer</td><td>Small data set (hundreds of images); safest and fastest</td></tr>
<tr><td>Fine-tuning</td><td>Also unfreeze some top pretrained layers and train them with a very small learning rate</td><td>More data, or when feature extraction plateaus</td></tr>
</tbody>
</table>
<p>The word fine-tuning is also used for language models: a general LLM is further trained on your own examples (for instance, questions and answers in a style you need). Smaller methods update only a small number of extra weights, which cuts the cost.</p>

<blockquote><p><strong>Quick check:</strong> You have 300 labelled photos. Should you train a large CNN from scratch or reuse a pretrained one?</p><p>Answer: Reuse a pretrained model, because 300 photos are too few to learn good features from scratch.</p></blockquote>

<h3>Keras example: three leaf classes</h3>
<p>Put photos in folders named by class, such as <code>leaves/healthy</code>, <code>leaves/rust</code> and <code>leaves/blight</code>.</p>
<pre><code>import keras
from keras import layers

size = (160, 160)
train = keras.utils.image_dataset_from_directory(
    "leaves", validation_split=0.2, subset="training",
    seed=42, image_size=size, batch_size=32)
val = keras.utils.image_dataset_from_directory(
    "leaves", validation_split=0.2, subset="validation",
    seed=42, image_size=size, batch_size=32)

base = keras.applications.MobileNetV2(
    input_shape=size + (3,), include_top=False, weights="imagenet")
base.trainable = False               # freeze the pretrained part

inputs = keras.Input(shape=size + (3,))
x = keras.applications.mobilenet_v2.preprocess_input(inputs)
x = base(x, training=False)
x = layers.GlobalAveragePooling2D()(x)
x = layers.Dropout(0.2)(x)
outputs = layers.Dense(3, activation="softmax")(x)
model = keras.Model(inputs, outputs)

model.compile(optimizer="adam",
              loss="sparse_categorical_crossentropy",
              metrics=["accuracy"])
model.fit(train, validation_data=val, epochs=5)</code></pre>
<p>The pretrained model needs its own input scaling, which <code>preprocess_input</code> performs. To fine-tune afterwards, set <code>base.trainable = True</code>, re-compile with a much smaller learning rate such as <code>keras.optimizers.Adam(1e-5)</code>, and train a few more epochs while watching validation loss.</p>

<blockquote><p><strong>Quick check:</strong> Why use a very small learning rate when fine-tuning?</p><p>Answer: Large steps would quickly destroy the useful features the pretrained layers already learned.</p></blockquote>

<h3>Scenario</h3>
<p>A youth group in Mbale photographs 150 images each of three common tomato leaf problems with phones. From scratch the model reaches 61 percent validation accuracy. With the frozen pretrained base it reaches 88 percent in five epochs on a free notebook. They keep a third set of 60 unseen photos taken by different people for the final test.</p>

<h3>Try it yourself</h3>
<ol>
<li>Collect at least 30 photos in each of two or three classes (for example three kinds of fruit) and put them in class-named folders.</li>
<li>Upload the folder to your Colab session.</li>
<li>Adapt the code above, changing the Dense layer to your number of classes.</li>
<li>Train for five epochs and note the validation accuracy.</li>
<li>Write which class gets confused most and one way to improve it.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Transfer learning reuses features learned from big data and needs far less of your own.</li>
<li>Freeze the base first; fine-tune later with a tiny learning rate if needed.</li>
<li>Keep an unseen, differently sourced test set for an honest final score.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Compute, cost and energy',
                    'minutes' => 15,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to estimate what a deep learning project will need in compute and money, and choose cheaper and greener options. This matters because many promising projects stall when the bill, data bundle cost or electricity is higher than expected, especially where power and internet are costly.</p>

<h3>Why deep learning is hungry</h3>
<p>Training repeats a huge number of multiplications. A <strong>GPU</strong> (graphics processing unit) does many of them at the same time, so it is much faster than an ordinary processor for this work. Special chips for AI exist as well. Training cost grows with the number of parameters, the size of the data set and the number of epochs.</p>

<h3>Where to run it</h3>
<table>
<thead><tr><th>Option</th><th>Strengths</th><th>Limits</th></tr></thead>
<tbody>
<tr><td>Your own laptop (CPU)</td><td>Free to use, private, works offline</td><td>Slow for big models</td></tr>
<tr><td>Free notebooks (Colab, Kaggle)</td><td>No cost, GPU often offered</td><td>Time limits, shared resources, need internet</td></tr>
<tr><td>Paid cloud GPU</td><td>Fast, flexible, pay per hour</td><td>Costs add up; you must switch it off</td></tr>
<tr><td>Own GPU computer</td><td>Full control</td><td>High purchase price and electricity</td></tr>
</tbody>
</table>

<h3>A rough cost estimate</h3>
<p>Suppose a job runs 20 epochs at 3 minutes each on a rented GPU costing the equivalent of 4,000 UGX per hour. The run takes 60 minutes, so about 4,000 UGX. If you try 15 settings during tuning, the bill becomes about 60,000 UGX, and uploading large data in repeated sessions may add data bundle costs. Plan the number of experiments before you start, and test on a small sample of the data first.</p>
<pre><code>total_cost = hours_per_run * price_per_hour * number_of_runs
           = 1 * 4000 * 15 = 60,000 UGX</code></pre>
<p>The price used is an illustration only; check current prices yourself.</p>

<blockquote><p><strong>Quick check:</strong> A run takes 30 minutes and costs 3,000 UGX per hour. You plan 8 runs. What is the cost?</p><p>Answer: 12,000 UGX, because 0.5 hours x 3,000 x 8 = 12,000.</p></blockquote>

<h3>Ways to save compute</h3>
<ul>
<li>Use transfer learning instead of training from scratch.</li>
<li>Start with a small model and a small sample of the data.</li>
<li>Reduce the image size, for example from 224 to 160 pixels.</li>
<li>Use early stopping so you do not train longer than needed.</li>
<li>Use mixed precision or quantisation where your library supports it.</li>
<li>Stop or disconnect idle notebooks and cloud machines.</li>
</ul>

<h3>Energy and the environment</h3>
<p>Electricity use depends on the hardware, the training time and how clean the power source is. Training very large models uses large amounts of energy, and serving millions of users adds up over time. For a small project the footprint is tiny, but good habits matter: choose the smallest model that works, avoid wasteful repeated runs and reuse models. Also consider fairness: heavy compute needs make it harder for people in low-resource settings to take part, which is a reason to value small, efficient models.</p>

<h3>Scenario</h3>
<p>A school club wants a model that recognises ten local birds. Option A trains a big model on a paid GPU for several hours; option B reuses a small pretrained model on a free notebook and finishes in 20 minutes with only 2 percent lower accuracy. They pick B, since the extra 2 percent is not worth the cost, and note this trade-off in their report.</p>

<blockquote><p><strong>Quick check:</strong> Name two ways to cut the compute needed for an image project.</p><p>Answer: For example, use transfer learning with a small pretrained model, and reduce image size or test on a sample first.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>In your Colab notebook, time one epoch of your leaf or digit model and multiply by the epochs you plan.</li>
<li>Write a cost estimate using an hourly price you find on a provider's current price page.</li>
<li>List three changes that would cut the training time in half.</li>
<li>Decide what accuracy loss you would accept in return for a model half the size.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Cost depends on model size, data size, epochs and number of experiments.</li>
<li>Free notebooks and transfer learning make deep learning affordable.</li>
<li>Smaller, efficient models save money and energy and widen access.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: In the MNIST example, why are pixel values divided by 255?
- To make the images bigger
* To scale values to the range 0 to 1 so training is smoother
- To remove the labels
- To convert colour images to text
= Pixel values run from 0 to 255; scaling them to 0-1 suits the network's weights and activations.

Q: Which number should you report as the final performance of your model?
- The best training accuracy seen in any epoch
- The validation accuracy you tuned on
* The accuracy on the untouched test set
- The loss at the first epoch
= Only the test set, which was never used for choices, gives an honest estimate.

Q: You have 250 photos per class for three classes and a normal laptop. What is the best approach?
- Train a very deep CNN from scratch
* Use a pretrained base with its layers frozen and train a new final layer
- Skip the validation set to have more training data
- Duplicate each photo 20 times and treat them as new data
= Transfer learning gives strong results from small data; duplicates would also cause leakage.

Q: Why use a very small learning rate when fine-tuning unfrozen pretrained layers?
- Pretrained layers cannot change at all
* To avoid destroying the useful features already learned
- To make the model larger
- Because the labels are not needed
= Large updates can wipe out good features; small steps adjust them gently.

Q: A tuning plan needs 12 runs of 45 minutes each on a GPU costing 4,000 UGX per hour. What is the approximate total cost?
- 12,000 UGX
* 36,000 UGX
- 48,000 UGX
- 4,000 UGX
= 12 runs x 0.75 hours = 9 hours, and 9 x 4,000 = 36,000 UGX.

Q: Which actions reduce the compute needed for a deep learning project? (choose all that apply)
* Test settings on a small sample of the data first
* Use a small pretrained model
- Leave a paid GPU running overnight in case you need it
* Stop training with early stopping when validation loss stops improving
= Sampling, reusing models and early stopping save time and money. Idle paid machines waste both.

Q: In a Keras script, what does model.fit(...) do?
- It deploys the model to a website
* It runs the training loop: forward pass, loss, backpropagation and weight updates
- It downloads the data set
- It only evaluates the model on test data
= fit trains the model; evaluate and predict are separate calls.
QUIZ,
        ],
        // ---------------------------------------------------------------- MODULE 5
        [
            'title'   => 'Module 5: Deployment and capstone project',
            'summary' => 'Save and serve a small model responsibly, then plan, build and report your own classifier.',
            'lessons' => [
                [
                    'title'   => 'Deploying a small model',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to save a trained model, load it for predictions and choose a sensible way to put it in front of users. This matters because a model sitting in a notebook helps nobody; the value comes when a farmer, customer or colleague can use it, safely and reliably.</p>

<h3>Save and load</h3>
<p>After training, save the architecture and weights to one file, then load it later without retraining.</p>
<pre><code>model.save("leaf_model.keras")

import keras
model = keras.models.load_model("leaf_model.keras")
probs = model.predict(batch_of_images)      # inference only</code></pre>
<p>Also save the list of class names in the same order as training (for example in a small text file), because the model outputs numbers, not names.</p>

<h3>Choosing where it runs</h3>
<table>
<thead><tr><th>Option</th><th>Good for</th><th>Watch out for</th></tr></thead>
<tbody>
<tr><td>On the phone (a compact format such as TensorFlow Lite)</td><td>Works offline, private, instant</td><td>Model must be small; updating means releasing a new app version</td></tr>
<tr><td>Small web service (API) on a server</td><td>One place to update, any device can use it</td><td>Needs internet, hosting cost, uploaded data to protect</td></tr>
<tr><td>Browser demo notebook or hosted demo page</td><td>Quick sharing for feedback</td><td>Not for sensitive data or heavy use</td></tr>
</tbody>
</table>

<blockquote><p><strong>Quick check:</strong> A rural clinic has weak internet. Which deployment suits an image-screening tool?</p><p>Answer: Running the model on the device, because it works offline and keeps images local.</p></blockquote>

<h3>A tiny web service</h3>
<p>This sketch uses Flask to receive a photo and return a label. It assumes the model contains its own preprocessing step, as in the transfer learning lesson.</p>
<pre><code>import numpy as np, keras
from flask import Flask, request, jsonify
from PIL import Image

model = keras.models.load_model("leaf_model.keras")
class_names = ["blight", "healthy", "rust"]
app = Flask(__name__)

@app.post("/predict")
def predict():
    img = Image.open(request.files["image"]).convert("RGB").resize((160, 160))
    arr = np.array(img, dtype="float32")[None, ...]
    probs = model.predict(arr)[0]
    i = int(probs.argmax())
    return jsonify({"label": class_names[i], "confidence": float(probs[i])})</code></pre>
<p>Class names must be in the same order the training data set used; Keras sorts folder names alphabetically. In real use you would add file size limits, error handling, and HTTPS.</p>

<h3>After launch: monitoring</h3>
<ul>
<li><strong>Drift:</strong> real-world data changes (new phone cameras, new seasons, new slang), so accuracy can quietly fall. Sample and check live predictions regularly.</li>
<li><strong>Feedback:</strong> give users an easy way to report wrong answers and use them to improve the data.</li>
<li><strong>Confidence:</strong> show low-confidence results as "not sure, ask an expert" rather than forcing an answer.</li>
<li><strong>Versioning:</strong> record which model version, data and settings produced each release so you can go back.</li>
<li><strong>Privacy and safety:</strong> store as little user data as possible, tell users how images are used and follow data protection law.</li>
</ul>

<h3>Scenario</h3>
<p>A model that spots maize disease worked at 90 percent in the notebook. Six months later farmers report poor results. Checking recent photos, the team finds they are mostly taken in the rainy season with wet leaves, which the original data barely contained. They add new labelled photos and retrain, then publish version 2 with a note on the change.</p>

<blockquote><p><strong>Quick check:</strong> What is data drift?</p><p>Answer: A change in real-world input data over time, so the model sees different data from what it was trained on and performs worse.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Save your trained model with <code>model.save("my_model.keras")</code> in Colab.</li>
<li>Restart the runtime, load the file with <code>keras.models.load_model</code> and predict on one image.</li>
<li>Write the class names in a text file in the correct order.</li>
<li>Write three rules you would give users on when not to trust the model.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Save the model and the class names together; load for inference without retraining.</li>
<li>Choose on-device or a web service by connectivity, privacy, size and update needs.</li>
<li>Monitor for drift, collect feedback and show uncertainty to users.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone planning: define the problem and the data',
                    'minutes' => 20,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to define a capstone project, choose success measures and plan your data before writing code. This matters because the best technical skills cannot rescue a vague problem or a poor data plan, and careful planning saves many hours of retraining.</p>

<h3>Choose a realistic project</h3>
<p>Your capstone is a classifier that is small enough to finish and useful enough to care about. Pick one of two tracks.</p>
<table>
<thead><tr><th>Track</th><th>Example ideas</th><th>Suggested method</th></tr></thead>
<tbody>
<tr><td>Image classifier</td><td>Healthy or diseased crop leaves, types of local fruit, damaged or good roads, plastic or glass waste</td><td>Transfer learning with a small pretrained CNN</td></tr>
<tr><td>Text classifier</td><td>Sorting customer messages into topics, spotting scam mobile money messages, sentiment of short reviews</td><td>A small embedding-based Keras model, or a pretrained text model if you can use one</td></tr>
</tbody>
</table>
<p>Good projects have 2 to 5 classes, data that you can collect yourself in a few days, and a clear user.</p>

<h3>The project brief</h3>
<p>Write a one-page brief answering these questions:</p>
<ol>
<li><strong>Problem and user:</strong> who will use the model and what decision does it support?</li>
<li><strong>Classes:</strong> what are the labels, with a definition of each?</li>
<li><strong>Data plan:</strong> where will examples come from, how many per class (aim for at least 100 each for images), and how will you get permission?</li>
<li><strong>Split plan:</strong> how will you separate training, validation and test, keeping related items together?</li>
<li><strong>Success measures:</strong> which numbers must you reach and which mistake is worse?</li>
<li><strong>Risks:</strong> bias, privacy, misuse and what happens when the model is wrong.</li>
</ol>

<blockquote><p><strong>Quick check:</strong> Your scam-message classifier must catch scams, but blocking real messages is also annoying. Which two measures should be in the brief?</p><p>Answer: Recall for scam messages (how many scams are caught) and precision (how many flagged messages are truly scams).</p></blockquote>

<h3>A sample brief</h3>
<pre><code>Project:   Leaf check for bean farmers in Kabale
User:      Extension officers using a phone in the field
Classes:   healthy, rust, angular leaf spot
Data:      120+ photos per class, taken by 3 people in 2 villages
Split:     test set = photos from a third village (unseen conditions)
Success:   test recall for each disease at least 80%
Risk:      wrong "healthy" result; app shows "ask an expert" below 70% confidence</code></pre>

<h3>Set up your baseline and timeline</h3>
<p>Plan to build a baseline quickly: a pretrained frozen model with one new layer. Then improve with augmentation, more data and fine-tuning. A realistic plan for a part-time learner is one week for data, one for training and evaluation, and one for packaging and report.</p>

<h3>Scenario</h3>
<p>A learner first wants to build "an AI that detects all plant diseases". Her mentor helps her narrow it to three diseases of one crop, in one region, for one user. The brief now has measurable goals, and data collection can start the same day. A smaller scope turned an impossible idea into a finishable project.</p>

<blockquote><p><strong>Quick check:</strong> Why is a test set from a different village a stronger check than a random split?</p><p>Answer: It shows whether the model works in new conditions, not just on photos similar to those it trained on.</p></blockquote>

<h3>Try it yourself</h3>
<ol>
<li>Choose a track and a project idea.</li>
<li>Write your one-page brief using the six questions.</li>
<li>Create a folder structure on your computer or Google Drive with one folder per class.</li>
<li>Collect your first 20 examples per class and check each against your label definitions.</li>
<li>Share the brief with a friend or mentor and note one improvement they suggest.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Pick a small, useful project with 2 to 5 classes and data you can gather.</li>
<li>The brief states the user, classes, data, split, success measures and risks.</li>
<li>Build a simple baseline first, then improve step by step.</li>
</ul>
HTML,
                ],
                [
                    'title'   => 'Capstone build, test and report',
                    'minutes' => 25,
                    'body'    => <<<'HTML'
<p>After this lesson you will be able to build your capstone classifier step by step, evaluate it honestly and write a short report that others can trust. This matters because a documented, honestly tested project is the best proof of your skill for a portfolio, a job application or a client.</p>

<h3>The build checklist</h3>
<ol>
<li><strong>Load and inspect the data.</strong> Count examples per class, view a sample of images or texts and remove duplicates and wrong labels.</li>
<li><strong>Split.</strong> Create training and validation sets, and keep a separate test set that you will use only once.</li>
<li><strong>Baseline.</strong> Train the simplest sensible model, such as a frozen pretrained base with a new output layer, for a few epochs. Record the scores.</li>
<li><strong>Improve one thing at a time.</strong> Add augmentation, dropout, more data or fine-tuning, and note each result in an experiment table.</li>
<li><strong>Diagnose.</strong> Plot training and validation curves and study the confusion matrix and wrongly predicted examples.</li>
<li><strong>Final test.</strong> Run the test set once and record accuracy, per-class precision and recall.</li>
<li><strong>Save</strong> the model and class names, and write a short <code>predict</code> function that takes one input and returns a label and confidence.</li>
</ol>

<h3>Getting the confusion matrix and report</h3>
<pre><code>import numpy as np
from sklearn.metrics import confusion_matrix, classification_report

y_true = np.concatenate([y for x, y in test_ds])
y_pred = np.argmax(model.predict(test_ds), axis=1)

print(confusion_matrix(y_true, y_pred))
print(classification_report(y_true, y_pred, target_names=class_names))</code></pre>
<p>This works when the test data set was created without shuffling, so the order of predictions matches the order of labels. With <code>image_dataset_from_directory</code> pass <code>shuffle=False</code> for the test set. The library scikit-learn is available in Colab.</p>

<blockquote><p><strong>Quick check:</strong> Why must the test data set not be shuffled in the code above?</p><p>Answer: The predictions are compared in order with the labels, so shuffling between the two steps would mismatch them and give wrong scores.</p></blockquote>

<h3>Writing the report</h3>
<p>A good report is two pages and answers these headings: problem and user, data (size, sources, labelling, permission), method (model, settings, what you tried), results (test accuracy plus precision and recall per class, a confusion matrix, a few example errors), limits (where it fails, who it may not work for), and next steps. This is sometimes called a model card.</p>
<table>
<thead><tr><th>Section</th><th>Example sentence</th></tr></thead>
<tbody>
<tr><td>Results</td><td>On 90 unseen photos from a third village, rust recall was 83 percent and precision 79 percent.</td></tr>
<tr><td>Limits</td><td>All photos were taken in daylight, so results at night are unknown.</td></tr>
<tr><td>Intended use</td><td>A helper for extension officers, not a replacement for expert diagnosis.</td></tr>
</tbody>
</table>

<h3>Scenario</h3>
<p>A learner's text classifier for mobile money messages scores 96 percent test accuracy. Looking at the confusion matrix she sees that 40 percent of real scam messages are missed, because scams were only 8 percent of her data. She adds more scam examples, uses class weights and re-tests. Accuracy drops to 94 percent but scam recall rises to 85 percent, which suits the purpose far better, and her report explains the change.</p>

<blockquote><p><strong>Quick check:</strong> Her accuracy fell from 96 to 94 percent. Was the second model worse?</p><p>Answer: No, it was better for the purpose, because catching scams (recall) mattered more than raw accuracy.</p></blockquote>

<h3>Share and reflect</h3>
<p>Publish your notebook and report where your instructor or community can see them, after removing private data and personal photos. Ask two people to try the model and note where they are confused. Reflect: what would you do with more data, more time or a larger budget?</p>

<h3>Try it yourself</h3>
<ol>
<li>Build the baseline for your project and record the validation accuracy.</li>
<li>Make two improvements, one at a time, and add them to an experiment table.</li>
<li>Run the test set once and generate the confusion matrix and classification report.</li>
<li>Write your two-page report using the headings above.</li>
<li>Submit your brief, experiment table and report to your instructor or mentor.</li>
</ol>

<h3>Key points</h3>
<ul>
<li>Follow the order: inspect, split, baseline, improve one thing at a time, diagnose, test once, save.</li>
<li>Judge with the confusion matrix, precision and recall that match the cost of mistakes.</li>
<li>An honest report with limits is more valuable than a high number with no context.</li>
</ul>
HTML,
                ],
            ],
            'quiz' => <<<'QUIZ'
Q: Why should you save the class names along with the model file?
- Class names make the model more accurate when it predicts
* The model outputs numbers, so you need the names in the same order as training to show a label
- The file cannot be loaded without a class names list
- Class names are needed to scale the pixel values
= Predictions are indexes; you must map each index to the right name.

Q: A clinic in a remote area has unreliable internet and wants private image screening. Which deployment fits best?
- A server API that needs a constant connection
- A hosted public demo page
* A compact model running on the device
- Emailing images to the developer
= On-device inference works offline and keeps images local.

Q: Six months after launch, accuracy falls because farmers now photograph leaves in the rainy season. What is this called?
- Overfitting to the test set
* Data drift
- Underfitting
- Leakage
= The real-world data changed from the training data, so performance dropped.

Q: Which design choice best protects users when a prediction is uncertain?
- Always show the top class as if it were certain
- Hide the confidence so users are not confused
* Show a message such as "not sure, ask an expert" below a chosen confidence level
- Retrain the model automatically after every prediction
= Showing uncertainty prevents false trust in low-confidence results.

Q: For a capstone, which project scope is most realistic?
- Detect every plant disease in the world
- Predict the national election
* Classify three diseases of one crop from phone photos in one region
- Build a new language model from scratch on a laptop
= A narrow problem with data you can collect and a clear user can actually be finished.

Q: Your classifier gets 96 percent accuracy, but only 8 percent of the data is scam messages and 40 percent of scams are missed. What should you do?
- Report 96 percent and finish
* Look at recall for the scam class, add more scam examples or class weights, and re-test
- Delete all non-scam messages from the test set
- Increase the batch size only
= With imbalanced data, accuracy hides poor recall on the rare class that matters most.

Q: When producing predictions for a confusion matrix, why must the test dataset not be shuffled between getting labels and predicting?
- Shuffling makes the model forget its weights
* The predictions and labels must be in the same order to be compared correctly
- Shuffled data cannot be scaled
- It would increase the learning rate
= If the order changes between the two steps, predictions are matched to the wrong labels.

Q: Which is the strongest sign of an honest capstone report?
- A single high accuracy number
* Test results per class, example errors, and clearly stated limits and intended use
- A long list of model layers only
- Removal of any test examples the model got wrong
= Honest evaluation shows where the model works, where it fails and how it should be used.
QUIZ,
        ],
    ],
    'final_exam' => <<<'QUIZ'
Q: A neuron has inputs 3 and 2, weights 0.5 and -1, and bias 1. What is its output before activation?
- 2.5
* 0.5
- 1.5
- -0.5
= (3 x 0.5) + (2 x -1) + 1 = 1.5 - 2 + 1 = 0.5.

Q: How many parameters are in a dense layer with 8 inputs and 6 neurons?
- 48
- 14
* 54
- 56
= 8 x 6 = 48 weights plus 6 biases is 54.

Q: Why would stacking many layers without activation functions fail to learn complex patterns?
- Each layer would erase the previous layer's output
* The whole stack would behave like a single straight-line formula
- The weights would all become zero
- The network would be too large to store
= Combining linear steps yields another linear step, so activations are needed for curves.

Q: What does ReLU output for the values -4, 0 and 6?
- -4, 0, 6
- 0, 0, 0
* 0, 0, 6
- 4, 0, 6
= ReLU turns negatives into 0 and keeps non-negative values.

Q: Which output design suits a model that must pick exactly one of seven classes?
- Seven neurons with ReLU
- One neuron with sigmoid
* Seven neurons with softmax
- One neuron with no activation
= Softmax gives seven probabilities that add to 1.

Q: A single example has input x = 2, target y = 10, weight w = 3 and prediction w x x. With the squared-error gradient 2 x (prediction - y) x x and learning rate 0.05, what is the new w?
- 2.2
- 3.2
* 3.8
- 4.28
= Prediction 6, error -4, gradient -16; new w = 3 - 0.05 x (-16) = 3.8.

Q: The loss curve zig-zags upwards and sometimes becomes extremely large. What is the most sensible first change?
- Increase the learning rate
* Decrease the learning rate
- Remove the loss function
- Use the test set for training
= Overshooting is caused by steps that are too large.

Q: There are 4,800 training examples with a batch size of 64. How many steps are in one epoch?
- 64
- 480
* 75
- 300
= 4,800 divided by 64 is 75.

Q: Which sentence about training and inference is correct?
- Inference repeats backpropagation to improve the weights
* Inference uses the fixed trained weights for the forward pass only
- Training only needs the forward pass
- Inference needs the labels of new data
= Weights are updated only during training.

Q: Training accuracy is 98 percent and validation accuracy is 71 percent. Which is the best action?
- Add more epochs
* Add augmentation, dropout, more varied data or early stopping
- Remove the validation set so the numbers look better
- Increase the learning rate tenfold
= The big gap indicates overfitting, which regularisation and more data address.

Q: You watched the test score while tuning the learning rate and dropout. Why is the final test score now unreliable?
- The test set is too small
* The test data influenced your choices, so it is no longer an independent check
- Test data cannot be used with Keras
- Dropout only works on validation data
= Tune on the validation set and use the test set once at the end.

Q: Photos of 30 patients are split at random by photo, and the test accuracy is very high. Later accuracy on new patients is poor. What probably happened?
- Underfitting
* Leakage: photos of the same patient were in training and test sets
- The learning rate was too small
- Softmax was used
= Group-related items should stay together so the test measures generalisation.

Q: A dataset has 97 percent healthy and 3 percent diseased examples. Which statement is most accurate?
- 97 percent accuracy proves the model is excellent
* A model that always predicts healthy would also score 97 percent, so check recall for the diseased class
- Class balance does not matter in deep learning
- Accuracy is the only measure that works with images
= Imbalanced data makes accuracy misleading.

Q: TP = 12, FN = 8, FP = 4, TN = 76. What is the precision?
- 60 percent
- 94 percent
* 75 percent
- 85 percent
= Precision = 12 / (12 + 4) = 75 percent. Recall would be 60 percent.

Q: A screening tool for a dangerous infection must avoid missing sick people, even if there are some false alarms. Which measure should be pushed up?
* Recall
- Precision
- Model size
- Batch size
= Recall measures how many real cases are found; lowering the decision threshold raises recall.

Q: Where should the average used to standardise a numeric column be computed?
- From the full data set before splitting
- From the test set
* From the training set only
- From each set separately and then swapped
= Using training statistics only avoids leakage.

Q: What is the key advantage of a convolutional layer for images?
- It needs no training
* It reuses the same small filters across the image, using fewer parameters and detecting patterns anywhere
- It stores a copy of every training image
- It ignores colour
= Weight sharing makes CNNs efficient and position-tolerant.

Q: A 5 x 5 filter with stride 1 and no padding is applied to a 20 x 20 image. What is the output size?
- 20 x 20
- 15 x 15
* 16 x 16
- 17 x 17
= (20 - 5) / 1 + 1 = 16.

Q: What do attention weights represent?
- The training accuracy of each token
* How much one token draws on each of the other tokens, as shares that add up to 1
- The position of each token on the page
- The number of times a word appears
= Attention uses softmax to produce a weighted mix of information.

Q: Why can the same message in a language with less training data cost more to process in an LLM?
- Languages are charged at different tax rates
* It may be split into more tokens, which uses more of the context window and compute
- The model refuses to read it
- Attention does not work on other languages
= Tokenisers often split less common languages into more pieces.

Q: Which is the right order of the stages for building an assistant-style LLM?
- Preference tuning, pretraining, instruction tuning, deployment
- Deployment, pretraining, preference tuning, instruction tuning
* Pretraining, instruction tuning, preference tuning, deployment
- Instruction tuning, deployment, pretraining, evaluation
= The base model is pretrained first, then tuned to follow instructions and preferences, then deployed.

Q: You have 200 photos per class for three classes and a free notebook. What gives the best chance of good results?
- Train a large CNN from scratch for 200 epochs
* Use a pretrained base with frozen layers and train a new output layer, then fine-tune gently if needed
- Use only the training set, with no validation
- Combine the three classes into one
= Transfer learning gives strong features from little data.

Q: What is a risk of fine-tuning pretrained layers with a large learning rate?
- The model cannot be saved
* The useful pretrained features may be destroyed quickly
- The model will use less memory
- Validation data will be ignored
= Use a very small learning rate so adjustments are gentle.

Q: An experiment needs 10 runs of 30 minutes on a GPU costing 6,000 UGX per hour. What is the total cost?
- 3,000 UGX
- 6,000 UGX
* 30,000 UGX
- 60,000 UGX
= 10 x 0.5 hours = 5 hours; 5 x 6,000 = 30,000 UGX.

Q: Which methods reduce the memory and compute needed for a model? (choose all that apply)
* Quantisation to fewer bits per weight
* Using a smaller pretrained model
- Doubling the number of layers
* Reducing the input image size
= Fewer bits, smaller models and smaller inputs cut cost; adding layers increases it.

Q: After deployment, a crop-disease model gradually gets worse because the weather and cameras changed. What should the team do?
- Nothing; trained models never degrade
* Monitor live predictions, collect feedback and new labelled data, and retrain a new version
- Delete the test set
- Hide the confidence scores
= Data drift is expected and needs monitoring and updates.

Q: Which is the best choice for a capstone project scope?
- A model that classifies every object in the world
* A three-class classifier with data you can collect and a clear user
- A language model trained from scratch on a laptop
- A project without a test set
= A small, specific scope can be finished, tested and explained honestly.

Q: In a report, which statement shows honest evaluation?
- Our model is 99 percent accurate.
* On 90 unseen photos from a third village, recall for rust was 83 percent, and night-time performance was not tested.
- The model never makes mistakes on farmers' photos.
- We removed the test photos the model got wrong.
= Specific results on unseen data with stated limits are honest and useful.
QUIZ,
];
