%{
/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: pasclang.l,v 2.5 2020-08-14 16:56:52 dick Exp $
*/

/*
	PASCAL language front end for the similarity tester.
	Author:	Maarten van der Meulen <maarten@cs.vu.nl>
	Date: May 1986
*/

#include	"global.h"
#include	"options.h"
#include	"token.h"
#include	"properties.h"
#include	"idf.h"
#include	"lex.h"
#include	"lang.h"

/* General language front end data */
Token lex_token;
size_t lex_nl_cnt;
size_t lex_tk_cnt;
size_t lex_non_ASCII_cnt;

/* Language-dependent data */

/* Data for module idf */

static const struct idf ppcmd[] = {
	{"define",	META('d')},
	{"else",	META('e')},
	{"endif",	META('E')},
	{"if",		META('i')},
	{"ifdef",	META('I')},
	{"ifndef",	META('x')},
	{"include",	MTCT('I')},
	{"line",	META('l')},
	{"undef",	META('u')}
};

static const struct idf reserved[] = {
	{"and",		NORM('&')},
	{"array",	NORM('A')},
	{"begin",	NORM('{')},
	{"case",	NORM('c')},
	{"const",	NORM('C')},
	{"div",		NORM('/')},
	{"do",		NORM('D')},
	{"downto",	NORM('d')},
	{"else",	NORM('e')},
	{"end",		NORM('}')},
	{"extern",	CTRL('E')},
	{"file",	NORM('F')},
	{"for",		NORM('f')},
	{"function",	NORM('p')},	/* Equal to procedure */
	{"goto",	NORM('g')},
	{"if",		NORM('i')},
	{"in",		NORM('I')},
	{"label",	NORM('l')},
	{"mod",		NORM('%')},
	{"nil",		NORM('n')},
	{"not",		NORM('!')},
	{"of",		No_Token},
	{"or",		NORM('|')},
	{"packed",	NORM('P')},
	{"procedure",	NORM('p')},
	{"program",	No_Token},
	{"record",	NORM('r')},
	{"repeat",	NORM('R')},
	{"set",		NORM('s')},
	{"then",	No_Token},
	{"to",		NORM('t')},
	{"type",	NORM('T')},
	{"until",	NORM('u')},
	{"var",		NORM('v')},
	{"while",	NORM('w')},
	{"with",	NORM('W')}
};

/* Special treatment of identifiers */

static Token
idf2token(int hashing) {
	Token tk;

	Lower_Case(yytext);		/* Pascal is case-insensitive */
	tk = Idf_In_List(yytext, reserved, sizeof reserved, IDF);
	if (Token_EQ(tk, IDF) && hashing) {
		/* return a one-Token hash code */
		tk = Idf_Hashed(yytext);
	}
	return tk;
}

/* Token sets for properties module */
const Token Non_Finals[] = {
	IDF,		/* identifier */
	NORM('{'),	/* also begin */
	NORM('('),
	NORM('['),
	NORM('A'),	/* array */
	NORM('c'),	/* case */
	NORM('C'),	/* const */
	NORM('/'),	/* div */
	CTRL('E'),	/* extern */
	NORM('F'),	/* file */
	NORM('f'),	/* for */
	NORM('g'),	/* goto */
	NORM('i'),	/* if */
	NORM('l'),	/* label */
	NORM('P'),	/* packed */
	NORM('p'),	/* procedure/function */
	NORM('r'),	/* record */
	NORM('R'),	/* repeat */
	NORM('s'),	/* set */
	NORM('T'),	/* type */
	NORM('v'),	/* var */
	NORM('w'),	/* while */
	NORM('W'),	/* with */
	No_Token
};
const Token Non_Initials[] = {
	NORM(')'),
	NORM('}'),
	NORM(';'),
	No_Token
};
const Token Openers[] = {
	NORM('{'),
	NORM('('),
	NORM('['),
	No_Token
};
const Token Closers[] = {
	NORM('}'),
	NORM(')'),
	NORM(']'),
	No_Token
};

/* Language-dependent code */

void
Init_Language(void) {
	Subject = "Pascal programs";
	Init_Language_Properties(Non_Finals, Non_Initials, Openers, Closers);
}

%}

%option	noyywrap

%Start	Comment

Layout		([ \t\r\f])
ASCII95		([\040-\176])

AnyQuoted	(\\.)
StrChar		([^\'\n\\]|{AnyQuoted})

StartComment	("{"|"(*")
EndComment	("}"|"*)")
SafeComChar	([^*}\n])
UnsafeComChar	("*")

Digit		([0-9])
Idf		([A-Za-z][A-Za-z0-9_]*)

%%

{StartComment}	{			/* See clang.l */
		BEGIN Comment;
	}

<Comment>{SafeComChar}+	{		/* safe comment chunk */
	}

<Comment>{UnsafeComChar}	{	/* unsafe char, read one by one */
	}

<Comment>"\n"		{		/* to break up long comments */
		return_eol();
	}

<Comment>{EndComment}	{		/* end-of-comment */
		BEGIN INITIAL;
	}

\'{StrChar}*\'	{			/* character strings */
		return_ch(STR);
	}

^#{Layout}*include.*	{		/* ignore #include lines */
	}

^#{Layout}*{Idf}	{		/* a preprocessor line */
		char *idf = yytext+1;

		/* skip layout in front of preprocessor identifier */
		while (*idf == ' ' || *idf == '\t') {
			idf++;
		}
		return_tk(Idf_In_List(idf, ppcmd, sizeof ppcmd, NORM('#')));
	}

{Digit}+	{			/* numeral, passed as an identifier */
		return_tk(IDF);
	}

{Idf}/"("	{			/* identifier in front of ( */
		Token tk;

		tk = idf2token(Is_Set_Option('F'));
		if (!Token_EQ(tk, No_Token)) return_tk(tk);
	}

{Idf}	{				/* identifier */
		Token tk;

		tk = idf2token(0 /* no hashing */);
		if (!Token_EQ(tk, No_Token)) return_tk(tk);
	}

\;	{				/* semicolon, conditionally ignored */
		if (Is_Set_Option('f')) return_ch(yytext[0]);
	}

\n	{				/* count newlines */
		return_eol();
	}

{Layout}	{			/* ignore layout */
	}

{ASCII95}	{			/* copy other text */
		return_ch(yytext[0]);
	}

.	{				/* count non-ASCII chars */
		lex_non_ASCII_cnt++;
	}

%%

/* More language-dependent code */

void
yystart(void) {
	BEGIN INITIAL;
}
