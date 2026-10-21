<?php
return [
    'slug' => 'online-safety-challenge', 'kind' => 'quiz',
    'title' => 'Online Safety and Cybersecurity Challenge', 'category' => 'security',
    'summary' => 'Test your knowledge of passwords, phishing, two-step login, privacy, scams, safe browsing and backups. 30 questions, 30 minutes, and a chance to be a Winner of the Month.',
    'description' => "The Online Safety and Cybersecurity Challenge checks whether you can protect yourself, your money and your information online. It covers strong passwords, multi-factor authentication, phishing and scams, privacy, safe browsing, backups and the basic ideas behind cybersecurity.\n\nYou will answer 30 questions in 30 minutes. Many questions describe real situations, such as a suspicious message about mobile money or a free Wi-Fi hotspot, and ask what you should do. Some questions say 'choose all that apply'.\n\nEveryone who reaches the pass score earns a certificate of participation. The three highest scores win, and the fastest finishing time breaks any tie.",
    'rules' => "The challenge is open to registered LAF Academy learners.\nOnly approved entrants can take part.\nYou have one attempt only.\nThe clock starts the moment you start the quiz and you have 30 minutes.\nAnswers are marked by the system as soon as you finish.\nThe highest score wins, and the fastest finishing time breaks a tie.\nWinners are announced and featured as Winners of the Month, each with a certificate.\nEvery entrant who scores at or above the pass score receives a certificate of participation.\nNo outside help and no sharing of answers. The decision of the system ranking is final.",
    'prize' => 'Winner of the Month feature on the LAF Academy, a certificate of achievement and a place on the Wall of Winners.',
    'winners_count' => 3, 'quiz_minutes' => 30, 'pass_score' => 50,
    'quiz' => <<<'QUIZ'
Q: Which of these is the strongest password?
- Uganda2024
- 12345678
- Password!
* Four or more unrelated words and some numbers, such as hill-lamp-river-7-chalk
= Long passwords made from several unrelated words are hard to guess and still easy to remember.

Q: You use the same password on your e-mail, your social media and your shop account. What is the main danger?
- You will forget it sooner
* If one site leaks it, attackers can try it on your other accounts
- The accounts will run slower
- Nothing, because it is easier to remember
= Attackers reuse leaked passwords on other sites. Use a different password for every important account.

Q: What is the best way to keep many different strong passwords?
- Write them all in a notebook labelled Passwords and carry it everywhere
- Send them to yourself by SMS
* Use a reputable password manager protected by one strong master password
- Use your birthday with a different number each time
= A password manager creates and stores strong unique passwords for you.

Q: What does MFA (multi-factor authentication) or two-step verification add to your login?
- A longer password
- A faster login
* A second, different proof of identity, such as a code from an app, in addition to your password
- A free antivirus
= Even if someone steals your password, they still need the second factor.

Q: Which of these is generally the safest second factor for your account?
- A code sent by someone who calls you and asks for it
* A code from an authenticator app or a hardware key that only you hold
- A code that you share with a friend
- Your date of birth
= Never read codes to anyone who contacts you. App-based codes and security keys are stronger than SMS codes.

Q: You get an SMS: "Your mobile money account is blocked. Send your PIN to this number to unblock it." What do you do?
- Send the PIN quickly before the account is lost
- Reply and ask for more details
* Do not reply, never share your PIN, and contact the provider through its official line
- Forward it to all your contacts as a warning with your PIN
= A genuine provider never asks for your PIN. This is a scam.

Q: What is phishing?
- Catching fish with a computer
* Tricking people into giving away passwords or money by pretending to be a trusted person or organisation
- A way to speed up the internet
- A type of backup
= Phishing messages often arrive by e-mail, SMS, social media or phone calls.

Q: Which of these are common signs of a phishing e-mail? (choose all that apply)
* Urgent pressure such as "act in 24 hours or lose your account"
* A sender address that does not match the organisation it claims to be
- A personal greeting from a colleague you know, about a meeting you arranged
* A link whose real address is different from the text shown
= Pressure, mismatched addresses and disguised links are classic signs. Check before you click.

Q: How can you safely check where a link goes before you click it on a computer?
- Click it and see
* Hover over it and read the address shown, or type the organisation's known address yourself
- Forward it to a friend and ask them to click it
- Trust it if it has many exclamation marks
= Typing the known address yourself is safer than following a link in a message.

Q: A stranger on social media becomes friendly and soon asks you to send money for a visa or urgent bill. What is this most likely?
- A normal friendship
- A government programme
* A romance or advance-fee scam
- A prize you have earned
= Scammers build trust and then ask for money. Never send money to someone you have not met and verified.

Q: A message says you won a lottery you never entered, and you must pay a fee to claim the prize. What is the best response?
- Pay a small fee in case it is real
- Send your ID to prove who you are
* Ignore and report it, because real prizes do not ask you to pay first
- Share it so friends can also pay
= Asking for upfront fees is a major sign of fraud.

Q: What does the padlock symbol and HTTPS in a browser address bar tell you?
- The website is owned by the government
- Everything on the site is true and safe
* Your connection to that site is encrypted, but the site itself can still be fake
- The site cannot contain malware
= Criminals also use HTTPS. Always check the domain name itself.

Q: You need to check your bank or mobile money account while using free public Wi-Fi at a cafe. What is the safest choice?
- Go ahead, because Wi-Fi is always protected
* Use your mobile data instead, or wait until you are on a trusted network
- Share the Wi-Fi password with everyone nearby
- Turn off your phone's screen lock
= Public Wi-Fi can be watched or copied by attackers. Avoid sensitive logins on it.

Q: Why should you install software updates?
- To change the colour of the screen
* They fix security weaknesses that attackers can use
- They always make the computer slower
- Updates are only for new users
= Many attacks use known flaws that an update has already closed.

Q: Where is it safest to download an app for your phone?
- A link a stranger sent you on WhatsApp
- A website offering "free premium" versions of paid apps
* The official app store of your phone
- A USB drive you found on the street
= Official stores check apps. Pirated or unknown files often carry malware.

Q: What is malware?
- A password manager
* Software designed to harm a device or steal information, such as viruses, spyware and ransomware
- A kind of backup
- A fast internet package
= "Malware" means malicious software.

Q: What does ransomware do?
- It speeds up your files
- It deletes spam
* It locks or encrypts your files and demands payment to give them back
- It improves your battery life
= Good offline backups and caution with attachments are the best protection.

Q: Which is the most reliable protection against losing files to ransomware, theft or a broken phone?
- Hoping it will not happen
- Keeping one copy only, on the same device
* Regular backups, including a copy that is kept offline or away from the device
- Renaming the files
= A common guideline is to keep several copies, in different places, and test that you can restore them.

Q: Which of these are good privacy settings and habits on social media? (choose all that apply)
* Limit who can see your posts and personal details
- Post your home address and daily routine
* Think before posting photos of other people, especially children
- Accept every friend request
= Over-sharing helps scammers and criminals. Share less and control who sees it.

Q: A website asks for your national ID number and your mother's maiden name to enter a competition. What should you think?
- Share it, since competitions are always safe
* This is more personal data than needed and could be used for identity theft, so be cautious
- Share only the first half
- Post it publicly so people know it is you
= Only give information that is really needed, and only to organisations you trust.

Q: What is social engineering?
- Building websites for social groups
* Manipulating people, instead of breaking technology, to give up secrets or access
- Designing social media pages
- Repairing computers in communities
= Many attacks target human trust and pressure, so awareness matters.

Q: In the "CIA triad" of information security, what do the letters stand for?
- Control, Internet, Access
* Confidentiality, Integrity, Availability
- Copy, Install, Archive
- Cloud, Identity, Authentication
= Keep data private, keep it correct and unchanged, and make sure it can be reached when needed.

Q: What does a firewall do?
- It removes dust from a computer
- It makes passwords longer
* It filters network traffic and blocks connections that are not allowed
- It repairs broken files
= A firewall sits between a device or network and the internet to control what gets through.

Q: What is the difference between authentication and authorisation?
- They mean exactly the same
* Authentication proves who you are; authorisation decides what you are allowed to do
- Authentication is for apps and authorisation is for phones
- Authentication decides permissions and authorisation checks passwords
= First the system checks your identity, then it checks your permissions.

Q: You lose your phone, which has your e-mail and mobile money apps. What are sensible first steps?
- Wait a week to see if someone returns it
* Contact your mobile network and provider to block the SIM and wallet, then change your passwords from another device
- Post the PIN on social media to find it
- Buy a new phone and say nothing
= Act quickly. Block access first, then secure your accounts. A screen lock and remote-wipe options also help.

Q: Which is the best action if you think you clicked a phishing link and typed your password?
- Do nothing and hope
* Change that password at once, using the real site, and on any other site that used it, then turn on MFA
- Delete the browser
- Tell the sender your new password
= Quick action limits damage. Also report the message and watch for strange activity.

Q: A colleague says to turn off your antivirus because it is slowing down a "free" program he found. What is the best response?
- Turn it off and install the program
- Turn it off only on Fridays
* Keep it on and avoid pirated software, which often hides malware
- Delete the operating system
= The free program may be the infection. Security tools should be kept running and updated.

Q: When you leave a shared computer, such as at a cybercafe or school lab, what should you do?
- Leave your accounts open so the next person does not need to log in
* Log out of all accounts, close the browser and do not save passwords
- Save your password in the browser for convenience
- Switch off only the monitor
= Anyone using the machine afterwards could otherwise open your accounts.

Q: Which statement about the "principle of least privilege" is correct?
- Everyone should be an administrator to avoid delays
- Passwords should be shared among all staff
* Users should have only the access they need to do their job and no more
- Only the youngest staff get access
= Limiting access reduces the damage from mistakes or stolen accounts.

Q: Someone calls and says they are from your bank and asks you to read out the one-time code that was just sent to your phone. What should you do?
- Read it out, since they obviously know your number
- Read only half of it
- Ask them to send an e-mail and then read it out
* End the call and contact the bank yourself using the number on your card or official website
= A one-time code is for you only. Real staff never need it, and callers can fake who they are.
QUIZ,
];
