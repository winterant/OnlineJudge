%{
/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: textlang.l,v 1.30 2020-08-14 16:56:53 dick Exp $
*/

/*
	Text front end for the similarity tester.
	Author:	Dick Grune <dick@cs.vu.nl>
	Date:	1986-06-02
*/

#include	"settings.par"
#include	"global.h"
#include	"options.h"
#include	"token.h"
#include	"idf.h"

#include	"lex.h"
#include	"lang.h"

/* General language front end data */
Token lex_token;
size_t lex_nl_cnt;
size_t lex_tk_cnt;
size_t lex_non_ASCII_cnt;		/* not applicable in text files */

/* Language-dependent code */

void
Init_Language(void) {
	Subject = "text";
	if (Is_Set_Option('f') || Is_Set_Option('F'))
		Fatal("options -f or -F not applicable in sim_text");
	Token_Name = "word";
	if (Threshold_Percentage == DEFAULT_THRESHOLD_PERCENTAGE)
		Threshold_Percentage = 20;
}

static Token
word2token(char *word) {
	/* ignore case */
	Lower_Case(word);
	return Idf_Hashed(word);
}

%}

%option	noyywrap


WordElem	([a-zA-Z0-9\200-\377])
TightWord	({WordElem}+)

NonWordElem	([^a-zA-Z0-9\200-\377])
LooseElem	({WordElem}(" "))
SpacedWord	({LooseElem}+{WordElem})

%%

{TightWord}	{
		return_tk(word2token(yytext));
	}

{SpacedWord}/{NonWordElem}	{
		/* the / operator works at the top level only */
		return_tk(word2token(yytext));
	}


\n	{				/* count newlines */
		return_eol();
	}

.	{				/* ignore the rest */
		/* UTF-8 bytes are included in WordElem */
	}

%%

/* More language-dependent code */

void
yystart(void) {
	BEGIN INITIAL;
}
